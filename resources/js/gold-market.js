const market = document.querySelector('[data-gold-market]');
if (market) {
  const number = value => new Intl.NumberFormat('fa-IR').format(Math.round(Number(value || 0)));
  const money = value => `${number(value)} تومان`;
  const pct = value => `${Number(value || 0).toLocaleString('fa-IR', {maximumFractionDigits: 2})}%`;

  function renderChart(points) {
    const svg = document.querySelector('#gold-live-chart');
    if (!svg || !points?.length) return;
    const width = 900, height = 300, pad = 28;
    const vals = points.map(p => Number(p.price));
    const min = Math.min(...vals), max = Math.max(...vals);
    const span = Math.max(max - min, 1);
    const xy = points.map((p, i) => {
      const x = pad + (i / Math.max(points.length - 1, 1)) * (width - pad * 2);
      const y = height - pad - ((Number(p.price) - min) / span) * (height - pad * 2);
      return [x, y];
    });
    const line = xy.map(([x,y]) => `${x.toFixed(1)},${y.toFixed(1)}`).join(' ');
    const area = `${pad},${height-pad} ${line} ${width-pad},${height-pad}`;
    svg.innerHTML = `
      <defs>
        <linearGradient id="goldChartFill" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="#d9ad5c" stop-opacity=".34"/>
          <stop offset="100%" stop-color="#d9ad5c" stop-opacity="0"/>
        </linearGradient>
        <filter id="goldGlow"><feGaussianBlur stdDeviation="4" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
      </defs>
      <polygon points="${area}" fill="url(#goldChartFill)"/>
      <polyline points="${line}" fill="none" stroke="#e1b867" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" filter="url(#goldGlow)"/>
      ${xy.slice(-1).map(([x,y])=>`<circle cx="${x}" cy="${y}" r="6" fill="#0a0806" stroke="#f0cb7b" stroke-width="3"/>`).join('')}
    `;
    const latest = points[points.length - 1];
    const label = document.querySelector('[data-gold-chart-latest]');
    if (label) label.textContent = money(latest.price);
  }

  async function refresh() {
    try {
      const res = await fetch('/api/gold-market', {headers:{Accept:'application/json'}, cache:'no-store'});
      if (!res.ok) throw new Error('market');
      const data = await res.json();
      market.querySelector('[data-gold18]').textContent = money(data.gold18_toman);
      market.querySelector('[data-change]').textContent = pct(data.change_percent);
      market.querySelector('[data-status]').textContent = data.live ? '● بازار زنده' : '● آخرین نرخ ذخیره‌شده';
      market.querySelector('[data-status]').classList.toggle('is-live', !!data.live);
      const ounce = market.querySelector('[data-ounce]');
      if (ounce) ounce.textContent = data.ounce_usd ? `$${Number(data.ounce_usd).toLocaleString('en-US', {maximumFractionDigits:2})}` : '—';
      const g24 = market.querySelector('[data-gold24]');
      if (g24) g24.textContent = data.gold24_toman ? money(data.gold24_toman) : '—';
      const updated = market.querySelector('[data-updated]');
      if (updated && data.updated_at) updated.textContent = new Intl.DateTimeFormat('fa-IR',{dateStyle:'short',timeStyle:'short'}).format(new Date(data.updated_at));
      renderChart(data.history);
    } catch (e) {
      const status = market.querySelector('[data-status]');
      if (status) status.textContent = '● اتصال بازار موقتاً ناموفق است';
    }
  }

  refresh();
  window.setInterval(refresh, 60000);
}
