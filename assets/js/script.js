// ✅ โหลด Header และ Footer
async function loadComponents() {
  const [headerRes, footerRes] = await Promise.all([
    fetch('../components/header.html'),
    fetch('../components/footer.html')
  ]);

  document.getElementById('header').innerHTML = await headerRes.text();
  document.getElementById('footer').innerHTML = await footerRes.text();

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

loadComponents();
