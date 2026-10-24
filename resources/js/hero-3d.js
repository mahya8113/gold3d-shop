import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

const container = document.getElementById('hero-ring-viewer');

if (container && !container.dataset.initialized) {
  container.dataset.initialized = 'true';

  const scene = new THREE.Scene();
  const width = Math.max(container.clientWidth, 1);
  const height = Math.max(container.clientHeight, 1);
  const camera = new THREE.PerspectiveCamera(35, width / height, 0.01, 100);
  camera.position.set(0, 0.15, 3.4);

  const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
  renderer.setSize(width, height);
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.25;
  container.appendChild(renderer.domElement);

  scene.add(new THREE.HemisphereLight(0xffffff, 0x080808, 2.2));
  const keyLight = new THREE.DirectionalLight(0xffd27a, 5);
  keyLight.position.set(3, 4, 4);
  scene.add(keyLight);
  const fillLight = new THREE.PointLight(0xffb84d, 20, 10);
  fillLight.position.set(-3, 1, 2);
  scene.add(fillLight);
  const rimLight = new THREE.PointLight(0xffffff, 14, 8);
  rimLight.position.set(2, 2, -3);
  scene.add(rimLight);

  const controls = new OrbitControls(camera, renderer.domElement);
  controls.enableDamping = true;
  controls.dampingFactor = 0.055;
  controls.enablePan = false;
  controls.enableZoom = true;
  controls.enableRotate = true;
  controls.minDistance = 2.1;
  controls.maxDistance = 5;
  controls.autoRotate = true;
  controls.autoRotateSpeed = 1.15;
  controls.target.set(0, 0, 0);

  let ring = null;
  const loader = new GLTFLoader();
  loader.load('/models3d/ring-classic.glb', (gltf) => {
    ring = gltf.scene;
    const bounds = new THREE.Box3().setFromObject(ring);
    const size = bounds.getSize(new THREE.Vector3());
    const center = bounds.getCenter(new THREE.Vector3());
    ring.position.sub(center);
    const maxDimension = Math.max(size.x, size.y, size.z) || 1;
    ring.scale.setScalar(1.55 / maxDimension);
    ring.position.y -= 0.03;
    ring.rotation.x = 0.15;
    ring.traverse((object) => {
      if (!object.isMesh) return;
      object.castShadow = true;
      object.receiveShadow = true;
      const materials = Array.isArray(object.material) ? object.material : [object.material];
      materials.forEach((material) => {
        if (!material) return;
        if ('metalness' in material) material.metalness = Math.max(material.metalness ?? 0.8, 0.85);
        if ('roughness' in material) material.roughness = Math.min(material.roughness ?? 0.3, 0.22);
      });
    });
    scene.add(ring);
    container.classList.add('loaded');
    const loading = container.querySelector('.hero-ring-loading');
    if (loading) loading.remove();
  }, undefined, (error) => {
    console.error('Hero 3D model error:', error);
    const loading = container.querySelector('.hero-ring-loading');
    if (loading) loading.innerHTML = '<div class="hero-ring-error">مدل سه‌بعدی بارگذاری نشد؛ فایل GLB را بررسی کن.</div>';
  });

  const clock = new THREE.Clock();
  function animate() {
    requestAnimationFrame(animate);
    const elapsed = clock.getElapsedTime();
    fillLight.position.x = Math.sin(elapsed * 0.7) * 3;
    fillLight.position.z = Math.cos(elapsed * 0.7) * 3;
    rimLight.position.x = Math.cos(elapsed * 0.45) * 3;
    rimLight.position.z = Math.sin(elapsed * 0.45) * 3;
    if (ring) ring.position.y = -0.03 + Math.sin(elapsed * 1.2) * 0.035;
    controls.update();
    renderer.render(scene, camera);
  }
  animate();

  function resize() {
    const w = container.clientWidth;
    const h = container.clientHeight;
    if (!w || !h) return;
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    renderer.setSize(w, h);
  }
  if ('ResizeObserver' in window) new ResizeObserver(resize).observe(container);
  else window.addEventListener('resize', resize);
  resize();
}
