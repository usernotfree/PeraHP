const ratesToPhp = {
    PHP: 1,
    USD: 58.5,
    EUR: 63.2,
    JPY: 0.39,
    SGD: 43.4
};

const wallets = [
    { code: "PHP", name: "Philippine Peso", balance: 25000, accent: "neutral" },
    { code: "USD", name: "US Dollar", balance: 850, accent: "success" },
    { code: "EUR", name: "Euro", balance: 320, accent: "warning" },
    { code: "JPY", name: "Japanese Yen", balance: 45000, accent: "neutral" },
    { code: "SGD", name: "Singapore Dollar", balance: 440, accent: "success" }
];

let transactions = [
    { ref: "RCV-260701-214", type: "Receive", user: "Client Example", amount: 74930, currency: "PHP", status: "completed", date: "Jul 1, 2026" },
    { ref: "SEND-260630-A91", type: "Send", user: "Juan Dela Cruz", amount: 100, currency: "USD", status: "completed", date: "Jun 30, 2026" },
    { ref: "REQ-260629-K02", type: "Request", user: "Client Example", amount: 2500, currency: "PHP", status: "pending", date: "Jun 29, 2026" },
    { ref: "EXCH-260628-V19", type: "Exchange", user: "Maria Santos", amount: 50, currency: "EUR", status: "completed", date: "Jun 28, 2026" },
    { ref: "SEND-260627-R77", type: "Send", user: "Online Store", amount: 40, currency: "SGD", status: "failed", date: "Jun 27, 2026" }
];

const monthlyReport = [
    { month: "Jan", received: 42000, sent: 18000 },
    { month: "Feb", received: 51000, sent: 22000 },
    { month: "Mar", received: 47000, sent: 25000 },
    { month: "Apr", received: 69000, sent: 28000 },
    { month: "May", received: 61000, sent: 31000 },
    { month: "Jun", received: 74930, sent: 28440 }
];

function byId(id) { return document.getElementById(id); }
function escapeHtml(value) { return String(value).replace(/[&<>"']/g, function(c) { return {"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[c]; }); }
function money(amount, code) { return code + " " + (Number(amount) || 0).toLocaleString("en-US", {minimumFractionDigits:2, maximumFractionDigits:2}); }
function phpValue(amount, code) { return (Number(amount) || 0) * (ratesToPhp[code] || 1); }
function convert(amount, from, to) { return phpValue(amount, from) / (ratesToPhp[to] || 1); }
function findWallet(code) { return wallets.find(w => w.code === code); }
function setText(id, value) { const el = byId(id); if (el) el.textContent = value; }
function statusClass(s) { return s === "completed" ? "success" : s === "pending" ? "warning" : s === "failed" ? "danger" : "neutral"; }
function statusLabel(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
function todayLabel() { return new Intl.DateTimeFormat("en-US", {month:"short", day:"numeric", year:"numeric"}).format(new Date()); }
function referenceStamp() { const now = new Date(); return String(now.getFullYear()).slice(-2) + String(now.getMonth()+1).padStart(2,"0") + String(now.getDate()).padStart(2,"0"); }
function createReference(prefix) { return prefix + "-" + referenceStamp() + "-" + Math.random().toString(16).slice(2, 5).toUpperCase(); }

function showToast(message) {
    const toast = byId("toast");
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add("show");
    window.setTimeout(() => toast.classList.remove("show"), 2200);
}

function populateCurrencyOptions() {
    const selectIds = ["sendFrom", "sendTo", "requestCurrency", "exchangeFrom", "exchangeTo"];
    selectIds.forEach(id => {
        const select = byId(id);
        if (!select) return;
        select.innerHTML = wallets.map(w => `<option value="${w.code}">${w.code} - ${w.name}</option>`).join("");
    });
}

function renderMetrics() {
    if (!byId("totalBalance")) return;
    const total = wallets.reduce((sum, w) => sum + phpValue(w.balance, w.code), 0);
    const currentMonth = monthlyReport[monthlyReport.length - 1];
    setText("totalBalance", money(total, "PHP"));
    setText("monthlyReceived", money(currentMonth.received, "PHP"));
    setText("monthlySent", money(currentMonth.sent, "PHP"));
    setText("pendingCount", String(transactions.filter(t => t.type === "Request" && t.status === "pending").length));
}

function renderWallets() {
    const grid = byId("walletGrid");
    if (!grid) return;
    grid.innerHTML = wallets.map(w => `<div class="wallet-card"><div class="wallet-top"><span class="badge ${w.accent}">${w.code}</span><strong>${money(w.balance, w.code)}</strong></div><small>${escapeHtml(w.name)} - 1 ${w.code} = PHP ${ratesToPhp[w.code].toFixed(2)}</small><small>PHP value: ${money(phpValue(w.balance, w.code), "PHP")}</small></div>`).join("");
}

function renderRates() {
    const grid = byId("rateGrid");
    if (!grid) return;
    grid.innerHTML = wallets.map(w => `<article class="rate-card"><span>${w.code}</span><strong>1 ${w.code} = PHP ${ratesToPhp[w.code].toFixed(2)}</strong><small>Used for wallet totals, transfers, exchanges, and reports.</small></article>`).join("");
}

function renderActivity() {
    const list = byId("activityList");
    if (!list) return;
    list.innerHTML = transactions.slice(0, 5).map(t => `<div class="activity-row"><div><strong>${escapeHtml(t.type)}</strong><small>${escapeHtml(t.ref)} - ${escapeHtml(t.user)}</small></div><div><strong class="activity-amount">${money(t.amount, t.currency)}</strong><span class="badge ${statusClass(t.status)}">${statusLabel(t.status)}</span></div></div>`).join("");
}

function renderTransactions() {
    const table = byId("transactionTable");
    if (!table) return;
    const search = (byId("searchInput")?.value || "").toLowerCase();
    const status = byId("statusFilter")?.value || "all";
    const rows = transactions.filter(t => (t.ref + " " + t.type + " " + t.user + " " + money(t.amount, t.currency) + " " + t.status).toLowerCase().includes(search) && (status === "all" || t.status === status));
    table.innerHTML = rows.map(t => `<tr><td><strong>${escapeHtml(t.ref)}</strong></td><td>${escapeHtml(t.type)}</td><td>${escapeHtml(t.user)}</td><td>${money(t.amount, t.currency)}</td><td><span class="badge ${statusClass(t.status)}">${statusLabel(t.status)}</span></td><td>${escapeHtml(t.date)}</td></tr>`).join("") || "<tr><td colspan=\"6\">No transactions found.</td></tr>";
}

function renderReport() {
    const chart = byId("reportChart");
    if (!chart) return;
    const max = Math.max(...monthlyReport.map(i => Math.max(i.received, i.sent)));
    chart.innerHTML = monthlyReport.map(i => `<div class="bar-row"><strong>${i.month}</strong><div class="bar-track"><div class="bar in" style="width:${Math.max(3, (i.received/max)*100)}%"></div><div class="bar out" style="width:${Math.max(3, (i.sent/max)*100)}%"></div></div><small>${money(i.received, "PHP")} in / ${money(i.sent, "PHP")} out</small></div>`).join("");
}

function updateSendPreview() {
    if (!byId("sendAmount")) return;
    const amount = Number(byId("sendAmount").value) || 0;
    const converted = convert(amount, byId("sendFrom").value, byId("sendTo").value);
    setText("sendPreview", money(converted, byId("sendTo").value));
    setText("sendPhpValue", "PHP base value: " + money(phpValue(amount, byId("sendFrom").value), "PHP"));
}

function updateExchangePreview() {
    if (!byId("exchangeAmount")) return;
    const amount = Number(byId("exchangeAmount").value) || 0;
    setText("exchangePreview", money(convert(amount, byId("exchangeFrom").value, byId("exchangeTo").value), byId("exchangeTo").value));
}

function renderAll() {
    renderMetrics(); renderWallets(); renderRates(); renderActivity(); renderTransactions(); renderReport();
}

function bindEvents() {
    const menu = byId("menuButton"); const side = byId("sidebar");
    if (menu && side) menu.addEventListener("click", () => side.classList.toggle("open"));
    document.querySelectorAll(".nav-link").forEach(l => l.addEventListener("click", () => { document.querySelectorAll(".nav-link").forEach(i => i.classList.remove("active")); l.classList.add("active"); side?.classList.remove("open"); }));
    document.querySelectorAll("[data-jump]").forEach(b => b.addEventListener("click", () => document.querySelector(b.dataset.jump)?.scrollIntoView({behavior:"smooth"})));
    
    ["sendAmount", "sendFrom", "sendTo"].forEach(id => byId(id)?.addEventListener("input", updateSendPreview));
    ["exchangeAmount", "exchangeFrom", "exchangeTo"].forEach(id => byId(id)?.addEventListener("input", updateExchangePreview));
    
    byId("searchInput")?.addEventListener("input", renderTransactions);
    byId("statusFilter")?.addEventListener("change", renderTransactions);
    byId("printButton")?.addEventListener("click", () => window.print());
    byId("logoutButton")?.addEventListener("click", () => showToast("Mock logout only."));

    byId("sendForm")?.addEventListener("submit", (e) => {
        e.preventDefault();
        const amt = Number(byId("sendAmount").value);
        const from = byId("sendFrom").value;
        const wallet = findWallet(from);
        if (amt > 0 && wallet && wallet.balance >= amt) {
            wallet.balance -= amt;
            transactions.unshift({ref:createReference("SEND"), type:"Send", user:byId("recipientEmail")?.value || "Recipient", amount:amt, currency:from, status:"completed", date:todayLabel()});
            renderAll(); showToast("Payment sent.");
        } else showToast("Insufficient funds or invalid amount.");
    });

    byId("requestForm")?.addEventListener("submit", (e) => {
        e.preventDefault();
        const ref = createReference("REQ");
        transactions.unshift({ref:ref, type:"Request", user:byId("payerEmail")?.value || "Payer", amount:Number(byId("requestAmount").value), currency:byId("requestCurrency").value, status:"pending", date:todayLabel()});
        setText("referenceCode", ref); renderAll(); showToast("Reference generated.");
    });

    byId("exchangeForm")?.addEventListener("submit", (e) => {
        e.preventDefault();
        const amt = Number(byId("exchangeAmount").value);
        const from = byId("exchangeFrom").value, to = byId("exchangeTo").value;
        const fW = findWallet(from), tW = findWallet(to);
        if (amt > 0 && fW && tW && fW.balance >= amt && from !== to) {
            fW.balance -= amt; tW.balance += convert(amt, from, to);
            transactions.unshift({ref:createReference("EXCH"), type:"Exchange", user:"Wallet exchange", amount:amt, currency:from, status:"completed", date:todayLabel()});
            renderAll(); showToast("Exchange complete.");
        } else showToast("Invalid exchange.");
    });
}

function initializeDashboard() {
    populateCurrencyOptions();
    renderAll();
    updateSendPreview();
    updateExchangePreview();
    bindEvents();
}

document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", initializeDashboard) : initializeDashboard();