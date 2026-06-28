const rupiah = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 });
const apiBase = "api";
let waNumber = "628993998544";

if (
  ["127.0.0.1", "localhost"].includes(window.location.hostname) &&
  window.location.port &&
  !["80", "8080"].includes(window.location.port)
) {
  window.location.replace(`http://${window.location.hostname}:8080${window.location.pathname}${window.location.search}${window.location.hash}`);
}

const defaultSettings = {
  brand_name: "Pilar Peradaban Dunia",
  brand_tagline: "Penerbit & Book Services",
  brand_mark: "PPD",
  meta_description: "Pilar Peradaban Dunia adalah penerbit buku, toko buku online, dan mitra produksi naskah untuk editing, layout, penerbitan, serta pencetakan buku.",
  hero_eyebrow: "Penerbit buku & toko resmi",
  hero_title: "Mengolah naskah menjadi buku yang siap dibaca, diterbitkan, dan dipasarkan.",
  hero_copy: "Pilar Peradaban Dunia mendampingi penulis, komunitas, lembaga, dan pesantren melalui layanan editing, layout, penerbitan, pencetakan, serta penjualan buku dalam katalog resmi.",
  hero_panel_text: "Satu jalur kerja untuk naskah, produksi, dan distribusi",
  hero_panel_strong: "Editor - Layout - Terbit - Cetak - Jual",
  whatsapp_number: "628993998544",
  footer_title: "Pilar Peradaban Dunia",
  footer_description: "Kanal resmi penerbit, toko buku online, dan layanan produksi naskah.",
  footer_link_text: "Hubungi WhatsApp",
  color_ink: "#111111",
  color_paper: "#f4eee4",
  color_panel: "#fffaf0",
  color_green: "#171717",
  color_red: "#9f1717",
  color_gold: "#c89b3c",
  color_cream: "#f8f0df"
};

const seedBooks = [
  {
    id: "ppd-menjawab-akidah-kaum-sufi",
    title: "Menjawab Akidah Kaum Sufi",
    author: "Abuya Syaikh Muhammad bin Alawy Al Maliky",
    isbn: "978-602-5607-45-7",
    price: 54600,
    priceLabel: "",
    stock: 998,
    source: "Terbitan PPD",
    category: "Agama",
    description: "Buku terjemahan 236 halaman berukuran 14,8 x 21 cm yang membahas dan menjawab persoalan akidah dalam tradisi kaum sufi."
  },
  {
    id: "ppd-terjemah-miftahul-ulum",
    title: "Terjemah Kitab Miftahul 'Ulum Fi Ta'rifil 'Ulum",
    author: "As-Syekh Ahmad bin Zakaria Al-Anshari As-Syafi'i",
    isbn: "PPD-MU-001",
    price: 0,
    priceLabel: "Hubungi Kami",
    stock: 18,
    source: "Terbitan PPD",
    category: "Agama",
    description: "Terjemah kitab karya ulama besar mazhab Syafi'i yang memuat pembahasan tentang ragam ilmu dan pengenalannya."
  },
  {
    id: "ppd-manual-kursus-aswaja",
    title: "Manual Kursus Aswaja",
    author: "Tim Penyusun Pilar Peradaban Dunia",
    isbn: "PPD-ASW-001",
    price: 0,
    priceLabel: "Hubungi Kami",
    stock: 12,
    source: "Terbitan PPD",
    category: "Agama",
    description: "Bahan ajar ringkas untuk kursus Aswaja, cocok sebagai pegangan belajar komunitas dan majelis."
  },
  {
    id: "ppd-obat-hati",
    title: "Obat Hati",
    author: "KH. Ahmad Zainuddin As-Sumbawi",
    isbn: "PPD-OH-001",
    price: 125000,
    priceLabel: "",
    stock: 9,
    source: "Terbitan PPD",
    category: "Agama",
    description: "Buku keislaman tentang penyucian jiwa dan penguatan hati melalui nasihat ulama."
  }
];

let books = [];
let cart = JSON.parse(localStorage.getItem("ppd_cart")) || [];
let orders = [];
let siteSettings = { ...defaultSettings };
let pages = [];
let adminLoggedIn = false;
let csrfToken = "";

const brandMark = document.querySelector("#brandMark");
const brandName = document.querySelector("#brandName");
const brandTagline = document.querySelector("#brandTagline");
const navLinks = document.querySelector("#navLinks");
const heroEyebrow = document.querySelector("#heroEyebrow");
const heroTitle = document.querySelector("#heroTitle");
const heroCopy = document.querySelector("#heroCopy");
const heroPanelText = document.querySelector("#heroPanelText");
const heroPanelStrong = document.querySelector("#heroPanelStrong");
const headerWa = document.querySelector("#headerWa");
const serviceWa = document.querySelector("#serviceWa");
const customPages = document.querySelector("#customPages");
const footerTitle = document.querySelector("#footerTitle");
const footerDescription = document.querySelector("#footerDescription");
const footerWa = document.querySelector("#footerWa");
const bookGrid = document.querySelector("#bookGrid");
const searchInput = document.querySelector("#searchInput");
const sourceFilter = document.querySelector("#sourceFilter");
const bookDetailSection = document.querySelector("#detail-buku");
const bookDetail = document.querySelector("#bookDetail");
const cartDrawer = document.querySelector("#cartDrawer");
const cartItems = document.querySelector("#cartItems");
const cartCount = document.querySelector("#cartCount");
const cartTotal = document.querySelector("#cartTotal");
const orderList = document.querySelector("#orderList");
const orderFilter = document.querySelector("#orderFilter");
const adminLogin = document.querySelector("#adminLogin");
const adminPanel = document.querySelector("#adminPanel");
const adminTopbar = document.querySelector("#adminTopbar");
const loginForm = document.querySelector("#loginForm");
const logoutAdmin = document.querySelector("#logoutAdmin");
const exportData = document.querySelector("#exportData");
const identityForm = document.querySelector("#identityForm");
const themeForm = document.querySelector("#themeForm");
const footerForm = document.querySelector("#footerForm");
const pageForm = document.querySelector("#pageForm");
const adminBookList = document.querySelector("#adminBookList");
const adminPageList = document.querySelector("#adminPageList");
const coverPreview = document.querySelector("#coverPreview");
const productDetail = document.querySelector("#productDetail");
const portalBookCount = document.querySelector("#portalBookCount");
const portalPageCount = document.querySelector("#portalPageCount");
const portalOrderCount = document.querySelector("#portalOrderCount");

async function api(path, options = {}) {
  const isFormData = options.body instanceof FormData;
  const headers = isFormData ? (options.headers || {}) : { "Content-Type": "application/json", ...(options.headers || {}) };
  const method = (options.method || "GET").toUpperCase();
  if (csrfToken && method !== "GET") {
    headers["X-CSRF-Token"] = csrfToken;
  }
  const response = await fetch(`${apiBase}/${path}`, {
    ...options,
    credentials: "same-origin",
    headers
  });
  const contentType = response.headers.get("content-type") || "";
  if (!contentType.includes("application/json")) {
    const text = await response.text();
    const looksLikeHtml = text.trim().startsWith("<!DOCTYPE") || text.trim().startsWith("<html");
    if (looksLikeHtml) {
      throw new Error("API PHP tidak berjalan. Buka website dari XAMPP/PHP server: http://127.0.0.1:8080/admin.html, bukan dari Live Server atau preview editor.");
    }
    throw new Error("Respons server bukan JSON. Periksa konfigurasi PHP/MySQL.");
  }
  const data = await response.json();
  if (!response.ok || data.ok === false) {
    throw new Error(data.message || "Terjadi kesalahan server.");
  }
  return data;
}

function escapeHtml(value) {
  return String(value ?? "").replace(/[&<>"']/g, (char) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;"
  })[char]);
}

function attr(value) {
  return escapeHtml(value);
}

function sourceClass(source) {
  if (source === "Distributor") return "source-distributor";
  if (source === "Titipan") return "source-titipan";
  return "";
}

function isPpdBook(book) {
  return book.source === "Terbitan PPD";
}

function saveCart() {
  localStorage.setItem("ppd_cart", JSON.stringify(cart));
}

function setText(element, value) {
  if (element) element.textContent = value || "";
}

function setFormValues(form, values) {
  if (!form) return;
  [...form.elements].forEach((field) => {
    if (!field.name || values[field.name] === undefined) return;
    field.value = values[field.name] ?? "";
  });
}

function collectForm(form) {
  return Object.fromEntries(new FormData(form).entries());
}

function resetForm(form) {
  if (form && typeof form.reset === "function") form.reset();
}

function truncateWords(text, maxWords = 24) {
  const words = String(text || "").trim().split(/\s+/).filter(Boolean);
  if (words.length <= maxWords) return words.join(" ");
  return `${words.slice(0, maxWords).join(" ")}...`;
}

function catalogDescription(book) {
  return truncateWords(book.shortDescription || book.description || "Deskripsi akan segera dilengkapi.", 24);
}

function detailDescription(book) {
  return book.description || book.shortDescription || "Deskripsi buku akan segera dilengkapi.";
}

function renderCover(book, className = "cover") {
  if (book.coverImage) {
    return `<div class="${attr(className)} has-cover"><img src="${attr(book.coverImage)}" alt="Cover ${attr(book.title)}" loading="lazy"></div>`;
  }
  return `<div class="${attr(className)}"><strong>${escapeHtml(book.title)}</strong></div>`;
}

function bookUrl(book) {
  const localPhpOrigin = ["127.0.0.1", "localhost"].includes(window.location.hostname)
    ? `http://${window.location.hostname}:8080`
    : window.location.origin;
  return `${localPhpOrigin}/book.html?id=${encodeURIComponent(book.id)}`;
}

function updateCoverPreview(src) {
  if (!coverPreview) return;
  if (src) {
    coverPreview.innerHTML = `<img src="${attr(src)}" alt="Preview cover">`;
    coverPreview.classList.add("has-image");
    return;
  }
  coverPreview.textContent = "Belum ada cover";
  coverPreview.classList.remove("has-image");
}

function applySettings() {
  waNumber = siteSettings.whatsapp_number || defaultSettings.whatsapp_number;
  document.title = `${siteSettings.brand_name} | Penerbit, Toko Buku & Layanan Buku`;
  const meta = document.querySelector('meta[name="description"]');
  if (meta) meta.setAttribute("content", siteSettings.meta_description || defaultSettings.meta_description);

  setText(brandMark, siteSettings.brand_mark);
  setText(brandName, siteSettings.brand_name);
  setText(brandTagline, siteSettings.brand_tagline);
  setText(heroEyebrow, siteSettings.hero_eyebrow);
  setText(heroTitle, siteSettings.hero_title);
  setText(heroCopy, siteSettings.hero_copy);
  setText(heroPanelText, siteSettings.hero_panel_text);
  setText(heroPanelStrong, siteSettings.hero_panel_strong);
  setText(footerTitle, siteSettings.footer_title);
  setText(footerDescription, siteSettings.footer_description);
  setText(footerWa, siteSettings.footer_link_text);

  [headerWa, serviceWa, footerWa].forEach((link) => {
    if (link) link.href = `https://wa.me/${waNumber}`;
  });

  const root = document.documentElement;
  root.style.setProperty("--ink", siteSettings.color_ink || defaultSettings.color_ink);
  root.style.setProperty("--paper", siteSettings.color_paper || defaultSettings.color_paper);
  root.style.setProperty("--panel", siteSettings.color_panel || defaultSettings.color_panel);
  root.style.setProperty("--green", siteSettings.color_green || defaultSettings.color_green);
  root.style.setProperty("--red", siteSettings.color_red || defaultSettings.color_red);
  root.style.setProperty("--gold", siteSettings.color_gold || defaultSettings.color_gold);
  root.style.setProperty("--cream", siteSettings.color_cream || defaultSettings.color_cream);

  setFormValues(identityForm, siteSettings);
  setFormValues(themeForm, siteSettings);
  setFormValues(footerForm, siteSettings);
  updateStructuredData();
}

function updateStructuredData() {
  const schema = document.querySelector("#siteSchema");
  if (!schema || document.body.classList.contains("admin-page")) return;

  const baseUrl = "https://peradabandunia.id/";
  const organization = {
    "@type": ["Organization", "BookStore"],
    "@id": `${baseUrl}#organization`,
    "name": siteSettings.brand_name || defaultSettings.brand_name,
    "alternateName": "Yayasan Pilar Peradaban Dunia",
    "url": baseUrl,
    "logo": `${baseUrl}assets/logo-pilar.svg`,
    "image": `${baseUrl}assets/hero-penerbit.jpg`,
    "description": siteSettings.meta_description || defaultSettings.meta_description,
    "contactPoint": {
      "@type": "ContactPoint",
      "contactType": "customer service",
      "telephone": `+${waNumber}`,
      "areaServed": "ID",
      "availableLanguage": ["id"]
    }
  };

  const itemList = {
    "@type": "ItemList",
    "@id": `${baseUrl}#book-catalog`,
    "name": "Katalog Buku Pilar Peradaban Dunia",
    "itemListElement": books.slice(0, 50).map((book, index) => ({
      "@type": "ListItem",
      "position": index + 1,
      "item": {
        "@type": "Book",
        "@id": `${baseUrl}#book-${book.id}`,
        "name": book.title,
        "author": { "@type": "Person", "name": book.author },
        "isbn": book.isbn || undefined,
        "image": book.coverImage ? `${baseUrl}${book.coverImage}` : `${baseUrl}assets/logo-pilar.svg`,
        "description": book.description || book.shortDescription || undefined,
        "offers": {
          "@type": "Offer",
          "availability": Number(book.stock) > 0 ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
          "price": Number(book.price) || 0,
          "priceCurrency": "IDR",
          "url": `${baseUrl}book.php?id=${encodeURIComponent(book.id)}`
        }
      }
    }))
  };

  schema.textContent = JSON.stringify({
    "@context": "https://schema.org",
    "@graph": [
      organization,
      {
        "@type": "WebSite",
        "@id": `${baseUrl}#website`,
        "url": baseUrl,
        "name": siteSettings.brand_name || defaultSettings.brand_name,
        "publisher": { "@id": `${baseUrl}#organization` },
        "inLanguage": "id-ID"
      },
      {
        "@type": "WebPage",
        "@id": `${baseUrl}#webpage`,
        "url": baseUrl,
        "name": document.title,
        "isPartOf": { "@id": `${baseUrl}#website` },
        "about": { "@id": `${baseUrl}#organization` },
        "mainEntity": { "@id": `${baseUrl}#book-catalog` },
        "inLanguage": "id-ID"
      },
      itemList
    ]
  });
}

async function loadSettings() {
  try {
    const data = await api("settings.php");
    siteSettings = { ...defaultSettings, ...(data.settings || {}) };
  } catch (error) {
    siteSettings = { ...defaultSettings };
  }
  applySettings();
}

function renderPages() {
  if (document.body.classList.contains("admin-page")) {
    renderAdminPages();
    renderAdminStats();
    return;
  }
  if (!navLinks || !customPages) return;
  const fixedMenu = `
    <a href="about.php">Tentang</a>
    <a href="#layanan">Layanan</a>
    <a href="#katalog">Katalog</a>
  `;
  const pageMenu = pages
    .filter((page) => page.isMenu)
    .map((page) => `<a href="#page-${attr(page.slug)}">${escapeHtml(page.menuLabel)}</a>`)
    .join("");
  navLinks.innerHTML = `${fixedMenu}${pageMenu}<a href="admin.html">Admin</a>`;

  customPages.innerHTML = pages.map((page, index) => `
    <section class="section ${index % 2 ? "intro-band" : "catalog-section"}" id="page-${attr(page.slug)}">
      <div class="section-heading">
        <div>
          <p class="eyebrow">${escapeHtml(page.menuLabel)}</p>
          <h2>${escapeHtml(page.title)}</h2>
        </div>
      </div>
      <article class="custom-page-content">${escapeHtml(page.content).replace(/\n/g, "<br>")}</article>
    </section>
  `).join("");

  renderAdminPages();
  renderAdminStats();
}

async function loadPages() {
  try {
    const data = await api("pages.php");
    pages = data.pages || [];
  } catch (error) {
    pages = [];
  }
  renderPages();
}

async function loadBooks() {
  try {
    const data = await api("books.php");
    books = data.books || [];
  } catch (error) {
    books = seedBooks;
  }
  renderBooks();
  renderCart();
  renderProductPage();
  renderAdminBooks();
  renderAdminStats();
  updateStructuredData();
}

async function loadOrders() {
  if (!orderList || !orderFilter) {
    orders = [];
    renderAdminStats();
    return;
  }
  if (!adminLoggedIn) {
    orders = [];
    renderOrders();
    return;
  }

  try {
    const data = await api("orders.php");
    orders = data.orders || [];
  } catch (error) {
    orders = [];
    alert(error.message);
  }
  renderOrders();
  renderAdminStats();
}

function renderAdminAccess() {
  if (!adminLogin || !adminPanel) return;
  adminLogin.hidden = adminLoggedIn;
  adminPanel.hidden = !adminLoggedIn;
  adminLogin.setAttribute("aria-hidden", String(adminLoggedIn));
  adminPanel.setAttribute("aria-hidden", String(!adminLoggedIn));
  adminPanel.inert = !adminLoggedIn;
  logoutAdmin.hidden = !adminLoggedIn;
  exportData.hidden = !adminLoggedIn;
  adminTopbar?.classList.toggle("is-unlocked", adminLoggedIn);
  document.body.classList.toggle("admin-locked", !adminLoggedIn);
  document.body.classList.toggle("admin-unlocked", adminLoggedIn);
  if (!adminLoggedIn) {
    document.querySelectorAll(".admin-tab").forEach((tab) => tab.classList.toggle("active", tab.dataset.adminTab === "dashboard"));
    document.querySelectorAll(".admin-pane").forEach((pane) => pane.classList.toggle("active", pane.dataset.adminPane === "dashboard"));
  }
}

function renderAdminStats() {
  setText(portalBookCount, String(books.length));
  setText(portalPageCount, String(pages.length));
  setText(portalOrderCount, String(orders.length));
}

function renderBooks() {
  if (!bookGrid || !searchInput || !sourceFilter) {
    renderAdminBooks();
    renderAdminStats();
    return;
  }
  const query = searchInput.value.trim().toLowerCase();
  const selectedSource = sourceFilter.value;
  const visibleBooks = books.filter((book) => {
    const haystack = `${book.title} ${book.author} ${book.isbn} ${book.category}`.toLowerCase();
    const matchQuery = !query || haystack.includes(query);
    const matchSource = selectedSource === "all"
      || book.source === selectedSource
      || (selectedSource === "non-ppd" && !isPpdBook(book));
    return matchQuery && matchSource;
  });

  const statBooks = document.querySelector("#statBooks");
  if (statBooks) statBooks.textContent = books.length;

  const bookCard = (book) => `
    <article class="book-card" data-view-book="${attr(book.id)}" tabindex="0" role="button" aria-label="Lihat detail ${attr(book.title)}">
      ${renderCover(book)}
      <div class="book-body">
        <div class="book-meta">
          <span class="tag ${attr(sourceClass(book.source))}">${escapeHtml(book.source)}</span>
          <span class="tag">${escapeHtml(book.category)}</span>
        </div>
        <h3 class="book-title">${escapeHtml(book.title)}</h3>
        <p class="book-author">${escapeHtml(book.author)}</p>
        <p class="book-desc">${escapeHtml(catalogDescription(book))}</p>
        <div class="book-footer">
          <div>
            <div class="price">${escapeHtml(book.priceLabel || rupiah.format(book.price))}</div>
            <small>Stok ${escapeHtml(book.stock)}</small>
          </div>
          <button class="small-button" type="button" data-add="${attr(book.id)}" ${Number(book.stock) < 1 ? "disabled" : ""}>${book.priceLabel ? "Tanya" : "Tambah"}</button>
        </div>
      </div>
    </article>
  `;

  const renderGroup = (title, description, groupBooks, modifier, shouldShow) => shouldShow ? `
    <section class="book-group ${modifier}">
      <div class="book-group-head">
        <div>
          <p class="eyebrow">${groupBooks.length} buku</p>
          <h3>${title}</h3>
        </div>
        <p>${description}</p>
      </div>
      <div class="book-group-grid">
        ${groupBooks.map(bookCard).join("") || `<p class="empty-state">Belum ada buku di kelompok ini.</p>`}
      </div>
    </section>
  ` : "";

  const ppdBooks = visibleBooks.filter(isPpdBook);
  const nonPpdBooks = visibleBooks.filter((book) => !isPpdBook(book));
  const showPpdGroup = selectedSource === "all" || selectedSource === "Terbitan PPD";
  const showNonPpdGroup = selectedSource === "all" || selectedSource === "non-ppd" || selectedSource === "Distributor" || selectedSource === "Titipan";

  bookGrid.innerHTML = [
    renderGroup(
      "Terbitan Pilar Peradaban Dunia",
      "Buku yang diterbitkan langsung oleh Pilar Peradaban Dunia.",
      ppdBooks,
      "book-group-ppd",
      showPpdGroup
    ),
    renderGroup(
      "Toko Buku Online",
      "Katalog buku pilihan dari terbitan Pilar, distributor, dan titipan yang dapat dipesan langsung melalui website ini.",
      nonPpdBooks,
      "book-group-non-ppd",
      showNonPpdGroup
    )
  ].join("") || `<p class="empty-state">Tidak ada buku yang cocok.</p>`;
}

function renderBookDetail(book) {
  if (!bookDetail || !bookDetailSection) return;
  if (!book) return;

  bookDetail.innerHTML = `
    ${renderCover(book, "detail-cover")}
    <article class="detail-info">
      <button class="secondary-action compact" type="button" data-close-detail>Kembali ke Katalog</button>
      <div class="book-meta">
          <span class="tag ${attr(sourceClass(book.source))}">${escapeHtml(book.source)}</span>
          <span class="tag">${escapeHtml(book.category)}</span>
        </div>
      <h2>${escapeHtml(book.title)}</h2>
      <p class="detail-author">${escapeHtml(book.author)}</p>
      <div class="detail-facts">
        <div><span>Harga</span><strong>${escapeHtml(book.priceLabel || rupiah.format(book.price))}</strong></div>
        <div><span>Stok</span><strong>${escapeHtml(book.stock)}</strong></div>
        <div><span>ISBN / SKU</span><strong>${escapeHtml(book.isbn || "-")}</strong></div>
      </div>
      <p class="detail-description">${escapeHtml(detailDescription(book))}</p>
      <div class="detail-actions">
        <button class="primary-action" type="button" data-add="${attr(book.id)}" ${Number(book.stock) < 1 ? "disabled" : ""}>${book.priceLabel ? "Tanya via Keranjang" : "Tambah ke Keranjang"}</button>
        <a class="secondary-action" href="#katalog">Lihat Buku Lain</a>
      </div>
    </article>
  `;

  bookDetailSection.hidden = false;
  bookDetailSection.scrollIntoView({ behavior: "smooth", block: "start" });
}

function hideBookDetail() {
  if (!bookDetail || !bookDetailSection) return;
  bookDetailSection.hidden = true;
  bookDetail.innerHTML = "";
  document.querySelector("#katalog")?.scrollIntoView({ behavior: "smooth", block: "start" });
}

function renderProductPage() {
  if (!productDetail) return;

  const id = new URLSearchParams(window.location.search).get("id");
  const book = books.find((entry) => entry.id === id);

  if (!book) {
    document.title = "Buku tidak ditemukan | Pilar Peradaban Dunia";
    productDetail.innerHTML = `
      <div class="product-cover"><strong>Buku tidak ditemukan</strong></div>
      <article class="product-info">
        <a class="secondary-action compact" href="index.html#katalog">Kembali ke Katalog</a>
        <h1>Buku tidak ditemukan</h1>
        <p class="detail-description">Buku yang Anda cari tidak tersedia atau sudah tidak aktif.</p>
      </article>
    `;
    return;
  }

  document.title = `${book.title} | Pilar Peradaban Dunia`;
  const meta = document.querySelector('meta[name="description"]');
  if (meta) meta.setAttribute("content", truncateWords(book.shortDescription || book.description || `Detail buku ${book.title}`, 28));

  productDetail.innerHTML = `
    ${renderCover(book, "product-cover")}
    <article class="product-info">
      <a class="secondary-action compact" href="index.html#katalog">Kembali ke Katalog</a>
      <div class="book-meta">
        <span class="tag ${attr(sourceClass(book.source))}">${escapeHtml(book.source)}</span>
        <span class="tag">${escapeHtml(book.category)}</span>
      </div>
      <h1>${escapeHtml(book.title)}</h1>
      <p class="detail-author">${escapeHtml(book.author)}</p>
      <div class="detail-facts">
        <div><span>Harga</span><strong>${escapeHtml(book.priceLabel || rupiah.format(book.price))}</strong></div>
        <div><span>Stok</span><strong>${escapeHtml(book.stock)}</strong></div>
        <div><span>ISBN / SKU</span><strong>${escapeHtml(book.isbn || "-")}</strong></div>
      </div>
      <p class="detail-description">${escapeHtml(detailDescription(book))}</p>
      <div class="detail-actions">
        <button class="primary-action" type="button" data-add="${attr(book.id)}" ${Number(book.stock) < 1 ? "disabled" : ""}>${book.priceLabel ? "Tanya via Keranjang" : "Tambah ke Keranjang"}</button>
        <a class="secondary-action" href="https://wa.me/${waNumber}?text=${encodeURIComponent(`Halo Pilar Peradaban Dunia, saya ingin bertanya tentang buku ${book.title}`)}" target="_blank" rel="noreferrer">Tanya WhatsApp</a>
      </div>
    </article>
  `;
}

function renderCart() {
  if (!cartItems || !cartCount || !cartTotal) return;
  const enriched = cart.map((item) => ({ ...item, book: books.find((book) => book.id === item.id) })).filter((item) => item.book);
  const total = enriched.reduce((sum, item) => sum + Number(item.book.price) * item.qty, 0);
  const count = enriched.reduce((sum, item) => sum + item.qty, 0);

  cartCount.textContent = count;
  cartTotal.textContent = rupiah.format(total);
  cartItems.innerHTML = enriched.map((item) => `
    <div class="cart-item">
      <div>
        <strong>${escapeHtml(item.book.title)}</strong>
        <p class="book-author">${escapeHtml(item.book.priceLabel || rupiah.format(item.book.price))} x ${escapeHtml(item.qty)}</p>
      </div>
      <div class="qty-controls">
        <button type="button" data-dec="${attr(item.id)}" aria-label="Kurangi">-</button>
        <strong>${escapeHtml(item.qty)}</strong>
        <button type="button" data-inc="${attr(item.id)}" aria-label="Tambah">+</button>
      </div>
    </div>
  `).join("") || `<p class="book-author">Keranjang masih kosong.</p>`;
}

function addToCart(id) {
  const existing = cart.find((item) => item.id === id);
  if (existing) existing.qty += 1;
  else cart.push({ id, qty: 1 });
  saveCart();
  renderCart();
  if (cartDrawer) {
    cartDrawer.classList.add("open");
    cartDrawer.setAttribute("aria-hidden", "false");
  }
}

function updateQty(id, delta) {
  cart = cart.map((item) => item.id === id ? { ...item, qty: item.qty + delta } : item).filter((item) => item.qty > 0);
  saveCart();
  renderCart();
}

function renderOrders() {
  if (!orderList || !orderFilter) {
    renderAdminStats();
    return;
  }
  const selectedStatus = orderFilter.value;
  const visibleOrders = orders.filter((order) => selectedStatus === "all" || order.status === selectedStatus);
  orderList.innerHTML = visibleOrders.map((order) => `
    <article class="order-card">
      <strong>${escapeHtml(order.id)} - ${escapeHtml(order.name)}</strong>
      <p>${escapeHtml(order.phone)} | ${escapeHtml(order.address)}</p>
      <p>${escapeHtml(order.items.map((item) => `${item.title} (${item.qty})${item.priceLabel ? " - " + item.priceLabel : ""}`).join(", "))}</p>
      <p>Total: <strong>${escapeHtml(rupiah.format(order.total))}</strong> | Status: <strong>${escapeHtml(order.status)}</strong></p>
      <div class="order-actions">
        ${["Baru", "Diproses", "Dikirim", "Selesai"].map((status) => `<button class="small-button" type="button" data-status="${attr(status)}" data-order="${attr(order.id)}">${escapeHtml(status)}</button>`).join("")}
      </div>
    </article>
  `).join("") || `<p class="book-author">Belum ada order pada status ini.</p>`;
}

function renderAdminBooks() {
  if (!adminBookList) return;
  adminBookList.innerHTML = books.map((book) => `
    <article>
      <strong>${escapeHtml(book.title)}</strong>
      <p>${escapeHtml(book.author)} | ${escapeHtml(book.source)} | Stok ${escapeHtml(book.stock)}</p>
      <p>${escapeHtml(book.priceLabel || rupiah.format(book.price))} | ISBN/SKU: ${escapeHtml(book.isbn || "-")}</p>
      ${book.coverImage ? `<p>Cover: tersedia</p>` : `<p>Cover: belum diupload</p>`}
      <div class="admin-list-actions">
        <button class="small-button" type="button" data-edit-book="${attr(book.id)}">Edit</button>
        <button class="small-button" type="button" data-delete-book="${attr(book.id)}">Hapus</button>
      </div>
    </article>
  `).join("") || `<p class="book-author">Belum ada buku.</p>`;
}

function renderAdminPages() {
  if (!adminPageList) return;
  adminPageList.innerHTML = pages.map((page) => `
    <article>
      <strong>${escapeHtml(page.menuLabel)} - ${escapeHtml(page.title)}</strong>
      <p>#page-${escapeHtml(page.slug)} | Urutan ${escapeHtml(page.menuOrder)} | ${page.isMenu ? "Tampil di menu" : "Tidak tampil di menu"}</p>
      <div class="admin-list-actions">
        <button class="small-button" type="button" data-edit-page="${attr(page.id)}">Edit</button>
        <button class="small-button" type="button" data-delete-page="${attr(page.id)}">Hapus</button>
      </div>
    </article>
  `).join("") || `<p class="book-author">Belum ada page tambahan.</p>`;
}

async function createOrder(formData) {
  if (!cart.length) {
    alert("Keranjang masih kosong.");
    return;
  }

  const payload = {
    name: formData.get("name"),
    phone: formData.get("phone"),
    address: formData.get("address"),
    notes: formData.get("notes"),
    items: cart.map((item) => ({ id: item.id, qty: item.qty }))
  };

  const data = await api("orders.php", {
    method: "POST",
    body: JSON.stringify(payload)
  });

  const order = data.order;
  cart = [];
  saveCart();
  renderCart();
  if (adminLoggedIn) await loadOrders();

  const message = encodeURIComponent(
    `Halo Pilar Peradaban Dunia, saya ingin order:\n\n${order.items.map((item) => `- ${item.title} x${item.qty}${item.priceLabel ? " (" + item.priceLabel + ")" : ""}`).join("\n")}\n\nTotal sementara: ${rupiah.format(order.total)}\nNomor order: ${order.id}\nNama: ${order.name}\nWA: ${order.phone}\nAlamat: ${order.address}\nCatatan: ${order.notes || "-"}`
  );
  window.open(`https://wa.me/${waNumber}?text=${message}`, "_blank");
  alert(`Order ${order.id} berhasil dibuat dan tersimpan di database.`);
  cartDrawer.classList.remove("open");
}

document.addEventListener("click", async (event) => {
  const addId = event.target.dataset.add;
  const viewBookId = event.target.dataset.viewBook || event.target.closest("[data-view-book]")?.dataset.viewBook;
  const closeDetail = event.target.dataset.closeDetail !== undefined;
  const incId = event.target.dataset.inc;
  const decId = event.target.dataset.dec;
  const orderId = event.target.dataset.order;
  const status = event.target.dataset.status;
  const editBookId = event.target.dataset.editBook;
  const deleteBookId = event.target.dataset.deleteBook;
  const editPageId = event.target.dataset.editPage;
  const deletePageId = event.target.dataset.deletePage;
  const adminTab = event.target.dataset.adminTab;

  if (addId) {
    event.stopPropagation();
    addToCart(addId);
    return;
  }
  if (closeDetail) {
    hideBookDetail();
    return;
  }
  if (viewBookId) {
    const book = books.find((entry) => entry.id === viewBookId);
    if (book) window.location.href = bookUrl(book);
  }
  if (incId) updateQty(incId, 1);
  if (decId) updateQty(decId, -1);
  if (adminTab) {
    document.querySelectorAll(".admin-tab").forEach((tab) => tab.classList.toggle("active", tab.dataset.adminTab === adminTab));
    document.querySelectorAll(".admin-pane").forEach((pane) => pane.classList.toggle("active", pane.dataset.adminPane === adminTab));
  }
  if (editBookId) {
    const book = books.find((entry) => String(entry.id) === String(editBookId));
    const form = document.querySelector("#bookForm");
    if (book && form) {
      setFormValues(form, {
        id: book.id,
        existingCoverImage: book.coverImage || "",
        title: book.title,
        author: book.author,
        isbn: book.isbn || "",
        price: book.price,
        stock: book.stock,
        priceLabel: book.priceLabel || "",
        source: book.source,
        category: book.category,
        shortDescription: book.shortDescription || "",
        description: book.description || ""
      });
      updateCoverPreview(book.coverImage || "");
      document.querySelector('[data-admin-tab="books"]')?.click();
      form.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }
  if (deleteBookId) {
    if (!confirm("Hapus buku ini dari katalog?")) return;
    try {
      await api("books.php", {
        method: "DELETE",
        body: JSON.stringify({ id: deleteBookId })
      });
      await loadBooks();
      resetForm(document.querySelector("#bookForm"));
      updateCoverPreview("");
      alert("Buku berhasil dihapus dari katalog.");
    } catch (error) {
      alert(error.message);
    }
  }
  if (editPageId) {
    const page = pages.find((entry) => String(entry.id) === String(editPageId));
    if (page) {
      setFormValues(pageForm, {
        id: page.id,
        menuLabel: page.menuLabel,
        slug: page.slug,
        title: page.title,
        content: page.content,
        menuOrder: page.menuOrder,
        isMenu: page.isMenu ? "1" : "0"
      });
      document.querySelector('[data-admin-tab="pages"]').click();
    }
  }
  if (deletePageId) {
    if (!confirm("Hapus page ini dari website?")) return;
    try {
      await api("pages.php", {
        method: "DELETE",
        body: JSON.stringify({ id: deletePageId })
      });
      await loadPages();
      resetForm(pageForm);
      alert("Page berhasil dihapus.");
    } catch (error) {
      alert(error.message);
    }
  }
  if (orderId && status) {
    try {
      await api("orders.php", {
        method: "PUT",
        body: JSON.stringify({ id: orderId, status })
      });
      await loadOrders();
    } catch (error) {
      alert(error.message);
    }
  }
});

document.addEventListener("keydown", (event) => {
  if (!["Enter", " "].includes(event.key)) return;
  const card = event.target.closest?.("[data-view-book]");
  if (!card) return;
  event.preventDefault();
  const book = books.find((entry) => entry.id === card.dataset.viewBook);
  if (book) window.location.href = bookUrl(book);
});

document.querySelector(".cart-toggle")?.addEventListener("click", () => {
  if (!cartDrawer) return;
  cartDrawer.classList.add("open");
  cartDrawer.setAttribute("aria-hidden", "false");
});

document.querySelector("#closeCart")?.addEventListener("click", () => {
  cartDrawer.classList.remove("open");
  cartDrawer.setAttribute("aria-hidden", "true");
});

document.querySelector("#bookForm")?.addEventListener("submit", async (event) => {
  event.preventDefault();
  const data = new FormData(event.currentTarget);
  if (!data.get("id")) data.set("id", data.get("isbn") || `book-${Date.now()}`);

  try {
    await api("books.php", {
      method: "POST",
      headers: {},
      body: data
    });
    resetForm(event.currentTarget);
    updateCoverPreview("");
    await loadBooks();
    alert("Data buku berhasil disimpan ke database.");
  } catch (error) {
    alert(error.message);
  }
});

document.querySelector("#resetBookForm")?.addEventListener("click", () => {
  resetForm(document.querySelector("#bookForm"));
  updateCoverPreview("");
});

document.querySelector('input[name="coverImage"]')?.addEventListener("change", (event) => {
  const file = event.currentTarget.files?.[0];
  if (!file) {
    const existing = document.querySelector('input[name="existingCoverImage"]')?.value;
    updateCoverPreview(existing || "");
    return;
  }
  updateCoverPreview(URL.createObjectURL(file));
});

identityForm?.addEventListener("submit", async (event) => {
  event.preventDefault();
  try {
    const data = await api("settings.php", {
      method: "POST",
      body: JSON.stringify({ settings: collectForm(event.currentTarget) })
    });
    siteSettings = { ...siteSettings, ...(data.settings || {}) };
    applySettings();
    alert("Identitas website berhasil disimpan.");
  } catch (error) {
    alert(error.message);
  }
});

themeForm?.addEventListener("submit", async (event) => {
  event.preventDefault();
  try {
    const data = await api("settings.php", {
      method: "POST",
      body: JSON.stringify({ settings: collectForm(event.currentTarget) })
    });
    siteSettings = { ...siteSettings, ...(data.settings || {}) };
    applySettings();
    alert("Warna halaman berhasil disimpan.");
  } catch (error) {
    alert(error.message);
  }
});

footerForm?.addEventListener("submit", async (event) => {
  event.preventDefault();
  try {
    const data = await api("settings.php", {
      method: "POST",
      body: JSON.stringify({ settings: collectForm(event.currentTarget) })
    });
    siteSettings = { ...siteSettings, ...(data.settings || {}) };
    applySettings();
    alert("Footer berhasil diperbarui.");
  } catch (error) {
    alert(error.message);
  }
});

pageForm?.addEventListener("submit", async (event) => {
  event.preventDefault();
  const payload = collectForm(event.currentTarget);
  payload.isActive = 1;
  payload.isMenu = payload.isMenu === "1" ? 1 : 0;

  try {
    const data = await api("pages.php", {
      method: "POST",
      body: JSON.stringify(payload)
    });
    pages = data.pages || [];
    resetForm(event.currentTarget);
    renderPages();
    alert("Page dan menu berhasil disimpan.");
  } catch (error) {
    alert(error.message);
  }
});

document.querySelector("#resetPageForm")?.addEventListener("click", () => {
  resetForm(pageForm);
});

document.querySelector("#checkoutForm")?.addEventListener("submit", async (event) => {
  event.preventDefault();
  try {
    await createOrder(new FormData(event.currentTarget));
    resetForm(event.currentTarget);
  } catch (error) {
    alert(error.message);
  }
});

loginForm?.addEventListener("submit", async (event) => {
  event.preventDefault();
  const data = new FormData(event.currentTarget);

  try {
    const login = await api("login.php", {
      method: "POST",
      body: JSON.stringify({
        username: data.get("username"),
        password: data.get("password")
      })
    });
    csrfToken = login.csrfToken || csrfToken;
    adminLoggedIn = true;
    resetForm(event.currentTarget);
    await loadSettings();
    await loadPages();
    await loadBooks();
    await loadOrders();
    renderAdminAccess();
  } catch (error) {
    alert(error.message);
  }
});

logoutAdmin?.addEventListener("click", async () => {
  try {
    await api("logout.php", { method: "POST", body: "{}" });
  } catch (error) {
  }
  csrfToken = "";
  adminLoggedIn = false;
  orders = [];
  renderAdminAccess();
  renderOrders();
  renderAdminStats();
});

exportData?.addEventListener("click", () => {
  const blob = new Blob([JSON.stringify({ books, orders }, null, 2)], { type: "application/json" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = "pilar-peradaban-data.json";
  link.click();
  URL.revokeObjectURL(url);
});

searchInput?.addEventListener("input", renderBooks);
sourceFilter?.addEventListener("change", renderBooks);
orderFilter?.addEventListener("change", renderOrders);

(async function init() {
  if (!document.body.classList.contains("admin-page")) {
    await loadSettings();
    await loadPages();
    await loadBooks();
    await loadOrders();
    return;
  }

  document.body.classList.add("admin-locked");
  try {
    const session = await api("session.php");
    adminLoggedIn = Boolean(session.loggedIn);
    csrfToken = session.csrfToken || "";
  } catch (error) {
    adminLoggedIn = false;
  }

  renderAdminAccess();
  if (adminLoggedIn) {
    await loadSettings();
    await loadPages();
    await loadBooks();
    await loadOrders();
  }
})();
