<?php
namespace App\Http\Controllers;

use App\Models\{Product, Order, Category, Review};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\FulfillmentService;

class AdminController extends Controller
{
    public function dashboard()
    {
        $today = now()->startOfDay();
        $week = now()->copy()->subDays(6)->startOfDay();
        $month = now()->copy()->subDays(29)->startOfDay();

        $daily = collect(range(6, 0))->map(function ($days) {
            $date = now()->subDays($days)->startOfDay();
            $next = $date->copy()->addDay();
            return [
                'label' => $date->format('m/d'),
                'orders' => Order::whereBetween('created_at', [$date, $next])->count(),
                'sales' => (int) Order::where('status', 'paid')->whereBetween('created_at', [$date, $next])->sum('total_amount'),
            ];
        });

        $paidMonth = (int) Order::where('status', 'paid')->where('created_at', '>=', $month)->sum('total_amount');
        $paidWeek = (int) Order::where('status', 'paid')->where('created_at', '>=', $week)->sum('total_amount');
        $todayOrders = Order::where('created_at', '>=', $today)->count();

        $status = Order::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $topProducts = DB::table('order_items')
            ->select('product_name', DB::raw('SUM(quantity) as units'), DB::raw('SUM(quantity * unit_price) as revenue'))
            ->groupBy('product_name')->orderByDesc('units')->limit(5)->get();

        return view('admin.dashboard', [
            'products' => Product::count(),
            'activeProducts' => Product::where('is_active', true)->count(),
            'orders' => Order::count(),
            'sales' => (int) Order::where('status', 'paid')->sum('total_amount'),
            'customers' => DB::table('users')->where('is_admin', false)->count(),
            'reviewsPending' => Review::where('is_approved', false)->count(),
            'lowStock' => Product::where('stock', '<=', 3)->where('is_active', true)->count(),
            'ticketsOpen' => DB::table('support_tickets')->where('status', 'open')->count(),
            'customOrders' => DB::table('custom_orders')->whereIn('status', ['submitted', 'reviewing', 'quoted'])->count(),
            'paidMonth' => $paidMonth,
            'paidWeek' => $paidWeek,
            'todayOrders' => $todayOrders,
            'daily' => $daily,
            'status' => $status,
            'topProducts' => $topProducts,
        ]);
    }

    public function products(Request $request)
    {
        $query = Product::with('category')->latest();
        if ($request->filled('q')) $query->where(fn($q) => $q->where('name', 'like', '%'.$request->q.'%')->orWhere('slug', 'like', '%'.$request->q.'%'));
        if ($request->filled('stock') && $request->stock === 'low') $query->where('stock', '<=', 3);
        if ($request->filled('active')) $query->where('is_active', (bool) $request->active);
        return view('admin.products.index', ['products' => $query->paginate(20)->withQueryString(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'category_id'=>'nullable|exists:categories,id','name'=>'required|string|max:180','description'=>'nullable|string',
            'price'=>'required|integer|min:1','stock'=>'required|integer|min:0','model_path'=>'nullable|string|max:255','image_path'=>'nullable|string|max:500'
        ]);
        $d['slug'] = Str::slug($d['name']).'-'.Str::lower(Str::random(5));
        $d['is_active'] = true;
        Product::create($d);
        return back()->with('success','محصول جدید به کاتالوگ اضافه شد.');
    }

    public function update(Request $r, Product $product)
    {
        $d = $r->validate([
            'category_id'=>'nullable|exists:categories,id','name'=>'required|string|max:180','price'=>'required|integer|min:1',
            'stock'=>'required|integer|min:0','description'=>'nullable|string','image_path'=>'nullable|string|max:500','model_path'=>'nullable|string|max:255','is_active'=>'nullable|boolean'
        ]);
        $d['is_active'] = $r->boolean('is_active');
        $product->update($d);
        return back()->with('success','اطلاعات محصول به‌روزرسانی شد.');
    }

    public function toggleProduct(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return back()->with('success', $product->is_active ? 'محصول فعال شد.' : 'محصول از ویترین خارج شد.');
    }

    public function delete(Product $product)
    {
        $product->delete();
        return back()->with('success','محصول حذف شد.');
    }

    public function orders(Request $request)
    {
        $query = Order::with(['items','user','payment'])->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('q')) $query->where(fn($q) => $q->where('customer_name','like','%'.$request->q.'%')->orWhere('phone','like','%'.$request->q.'%')->orWhere('id',$request->q));
        return view('admin.orders.index', ['orders'=>$query->paginate(25)->withQueryString()]);
    }

    public function updateOrder(Request $r, Order $order, FulfillmentService $service)
    {
        $d=$r->validate(['status'=>'required|in:pending,paid,processing,shipped,completed,cancelled','tracking_code'=>'nullable|string|max:100','shipping_carrier'=>'nullable|string|max:100']);
        $service->transition($order,$d['status'],$d['tracking_code']??null,$d['shipping_carrier']??null);
        return back()->with('success','وضعیت سفارش #'.$order->id.' ذخیره شد.');
    }

    public function reviews()
    {
        return view('admin.reviews.index', ['reviews'=>Review::with(['product','user'])->latest()->paginate(25)]);
    }

    public function updateReview(Request $r, Review $review)
    {
        $review->update(['is_approved'=>$r->boolean('is_approved')]);
        return back()->with('success','وضعیت نظر ذخیره شد.');
    }

    public function customers(Request $request)
    {
        $query = DB::table('users')->where('is_admin', false);
        if ($request->filled('q')) $query->where(fn($q)=>$q->where('name','like','%'.$request->q.'%')->orWhere('email','like','%'.$request->q.'%')->orWhere('phone','like','%'.$request->q.'%'));
        $customers = $query->orderByDesc('created_at')->paginate(25)->withQueryString();
        $orderCounts = Order::select('user_id',DB::raw('COUNT(*) as orders'),DB::raw("SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as paid_total"))->groupBy('user_id')->get()->keyBy('user_id');
        return view('admin.customers.index', compact('customers','orderCounts'));
    }

    public function operations()
    {
        return view('admin.operations.index', [
            'tickets'=>DB::table('support_tickets')->latest()->paginate(15,['*'],'tickets'),
            'customOrders'=>DB::table('custom_orders')->latest()->paginate(15,['*'],'custom'),
            'alerts'=>DB::table('price_alerts')->where('is_active',true)->count(),
        ]);
    }

    public function updateTicket(Request $r, int $ticket)
    {
        $status=$r->validate(['status'=>'required|in:open,in_progress,resolved,closed'])['status'];
        DB::table('support_tickets')->where('id',$ticket)->update(['status'=>$status,'updated_at'=>now()]);
        return back()->with('success','وضعیت تیکت ذخیره شد.');
    }

    public function updateCustomOrder(Request $r, int $customOrder)
    {
        $d=$r->validate(['status'=>'required|in:submitted,reviewing,quoted,approved,in_production,completed,cancelled','quoted_amount'=>'nullable|integer|min:0']);
        DB::table('custom_orders')->where('id',$customOrder)->update($d+['updated_at'=>now()]);
        return back()->with('success','سفارش سفارشی به‌روزرسانی شد.');
    }

    public function audit()
    {
        return view('admin.audit.index',['logs'=>DB::table('audit_logs')->latest()->paginate(40)]);
    }
}
