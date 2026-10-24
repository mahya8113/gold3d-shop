import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { EffectComposer } from 'three/addons/postprocessing/EffectComposer.js';
import { RenderPass } from 'three/addons/postprocessing/RenderPass.js';
import { UnrealBloomPass } from 'three/addons/postprocessing/UnrealBloomPass.js';
import { OutputPass } from 'three/addons/postprocessing/OutputPass.js';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

const prefersReducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

function makeParticles(scene) {
  const count = 520;
  const positions = new Float32Array(count * 3);
  for (let i = 0; i < count; i++) {
    positions[i * 3] = (Math.random() - .5) * 8;
    positions[i * 3 + 1] = Math.random() * 5 - 1.5;
    positions[i * 3 + 2] = (Math.random() - .5) * 6;
  }
  const geometry = new THREE.BufferGeometry();
  geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
  const material = new THREE.PointsMaterial({ color: 0xe3bd73, size: .018, transparent: true, opacity: .48, depthWrite: false });
  const points = new THREE.Points(geometry, material);
  scene.add(points);
  return points;
}

function setupViewer(el) {
  const url = el.dataset.modelViewer;
  const fallbackImage = el.dataset.fallbackImage || 'https://images.unsplash.com/photo-1662833833862-4f5328756afa?auto=format&fit=crop&fm=jpg&q=88&w=1600';

  // If there is no real GLTF/GLB model, never manufacture the old white/procedural ring.
  // Show a real jewelry photograph instead.
  if (!url) {
    el.innerHTML = `
      <div class="real-photo-viewer">
        <img src="${fallbackImage}" alt="عکس واقعی جواهر" loading="eager" decoding="async" referrerpolicy="no-referrer">
        <div class="real-photo-viewer-overlay"></div>
        <div class="real-photo-viewer-badge">
          <span>REAL JEWELRY</span>
          <strong>عکس واقعی محصول</strong>
          <small>برای نمایش 3D واقعی، فایل GLB/GLTF محصول را بارگذاری کنید.</small>
        </div>
      </div>`;
    return;
  }

  const scene = new THREE.Scene();
  scene.background = new THREE.Color(0x070706);

  const camera = new THREE.PerspectiveCamera(34, 1, .1, 100);
  camera.position.set(0, 1.05, 4.2);

  const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: false, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.8));
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.18;
  renderer.shadowMap.enabled = true;
  renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  el.replaceChildren(renderer.domElement);

  const composer = new EffectComposer(renderer);
  composer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.6));
  const renderPass = new RenderPass(scene, camera);
  const bloom = new UnrealBloomPass(new THREE.Vector2(900, 600), .52, .7, .72);
  const output = new OutputPass();
  composer.addPass(renderPass);
  composer.addPass(bloom);
  composer.addPass(output);

  const hemi = new THREE.HemisphereLight(0xfff4d6, 0x080604, 1.55);
  scene.add(hemi);

  const key = new THREE.DirectionalLight(0xffe6b1, 4.4);
  key.position.set(3.2, 5.5, 4.2);
  key.castShadow = true;
  key.shadow.mapSize.set(1024, 1024);
  scene.add(key);

  const rim = new THREE.PointLight(0xd4e7ff, 3.2, 8);
  rim.position.set(-3.5, 1.8, 2.8);
  scene.add(rim);

  const warm = new THREE.PointLight(0xffa43a, 2.4, 7);
  warm.position.set(2.7, -1, -2.2);
  scene.add(warm);

  const floor = new THREE.Mesh(
    new THREE.CircleGeometry(3.5, 96),
    new THREE.MeshStandardMaterial({ color: 0x0c0a08, metalness: .72, roughness: .3 })
  );
  floor.rotation.x = -Math.PI / 2;
  floor.position.y = -.85;
  floor.receiveShadow = true;
  scene.add(floor);

  const group = new THREE.Group();
  group.rotation.z = -.08;
  scene.add(group);

  const particles = makeParticles(scene);
  const controls = new OrbitControls(camera, renderer.domElement);
  controls.enableDamping = true;
  controls.dampingFactor = .045;
  controls.autoRotate = !prefersReducedMotion;
  controls.autoRotateSpeed = .75;
  controls.enablePan = false;
  controls.minDistance = 2.65;
  controls.maxDistance = 6.2;
  controls.target.set(0, .25, 0);

  new GLTFLoader().load(url, loaded => {
      group.clear();
      group.add(loaded.scene);
      loaded.scene.traverse(o => {
        if (o.isMesh) {
          o.castShadow = true;
          o.receiveShadow = true;
          if (o.material?.isMeshStandardMaterial || o.material?.isMeshPhysicalMaterial) {
            o.material.envMapIntensity = 1.65;
          }
        }
      });
  }, undefined, error => {
    console.error('Gold3D 3D model failed to load:', error);
    el.innerHTML = `
      <div class="real-photo-viewer">
        <img src="${fallbackImage}" alt="عکس واقعی جواهر" loading="eager" decoding="async" referrerpolicy="no-referrer">
        <div class="real-photo-viewer-overlay"></div>
        <div class="real-photo-viewer-badge"><span>REAL JEWELRY</span><strong>پیش‌نمایش واقعی</strong><small>مدل 3D در دسترس نیست؛ تصویر واقعی محصول نمایش داده شد.</small></div>
      </div>`;
  });

  let raf = 0;
  const resize = () => {
    const w = Math.max(el.clientWidth, 280);
    const h = Math.max(el.clientHeight || 500, 320);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    renderer.setSize(w, h, false);
    composer.setSize(w, h);
  };
  resize();
  const ro = new ResizeObserver(resize);
  ro.observe(el);

  const clock = new THREE.Clock();
  const animate = () => {
    if (!el.isConnected) {
      cancelAnimationFrame(raf);
      ro.disconnect();
      controls.dispose();
      renderer.dispose();
      composer.dispose();
      return;
    }
    raf = requestAnimationFrame(animate);
    const t = clock.getElapsedTime();
    if (!prefersReducedMotion) {
      group.position.y = Math.sin(t * .8) * .035;
      particles.rotation.y = t * .018;
      particles.position.y = Math.sin(t * .22) * .06;
    }
    controls.update();
    composer.render();
  };
  animate();
}

document.querySelectorAll('[data-model-viewer]').forEach(setupViewer);
