// ✅ โหลด Header, Footer และ Icons
async function loadComponents() {
  const [headerRes, footerRes, iconsRes] = await Promise.all([
    fetch('../components/header.html'),
    fetch('../components/footer.html'),
    fetch('../components/icons.html')
  ]);

  document.getElementById('header').innerHTML = await headerRes.text();
  document.getElementById('footer').innerHTML = await footerRes.text();

  // ✅ สร้าง placeholder สำหรับ icons ถ้ายังไม่มี
  let iconPlaceholder = document.getElementById('icons-placeholder');
  if (!iconPlaceholder) {
    iconPlaceholder = document.createElement('div');
    iconPlaceholder.id = 'icons-placeholder';
    iconPlaceholder.style.display = 'none';
    document.body.appendChild(iconPlaceholder);
  }

  iconPlaceholder.innerHTML = await iconsRes.text();

  // ✅ ดึง icon ใส่จุดที่กำหนด
  ['email', 'phone', 'location'].forEach(type => {
    const icon = document.getElementById(`icon-${type}`);
    const target = document.getElementById(`${type}-icon`);
    if (icon && target) {
      target.innerHTML = icon.innerHTML;
    }
  });

  // ✅ เรียก toggle เมนูหลังจาก DOM ใน header โหลดแล้ว
  requestAnimationFrame(setupMobileMenu);
}

// ✅ เปิด/ปิดเมนูมือถือ
function setupMobileMenu() {
  const toggleBtn = document.getElementById("mobile-toggle");
  const mobileNav = document.getElementById("mobile-nav");

  if (toggleBtn && mobileNav) {
    toggleBtn.addEventListener("click", () => {
      mobileNav.classList.toggle("hidden");
    });
  }
}

// ✅ เริ่มโหลดทุกอย่าง
loadComponents();
