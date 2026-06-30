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
    { code: "SGD", name: "Singapore Dollar", balance: 440, accent: "success" }
];

let transactions = [
    { ref: "SEND-260629-A91", type: "Send", user: "Juan Dela Cruz", amount: "$100.00 USD", status: "completed", date: "Jun 29, 2026" },
    { ref: "REQ-260628-K02", type: "Request", user: "Client Example", amount: "PHP 2,500.00", status: "pending", date: "Jun 28, 2026" },
    { ref: "EXCH-260627-V19", type: "Exchange", user: "Maria Santos", amount: "€50.00 EUR", status: "completed", date: "Jun 27, 2026" },
    { ref: "SEND-260626-R77", type: "Send", user: "Online Store", amount: "S$40.00 SGD", status: "failed", date: "Jun 26, 2026" }
];

const monthlyReport = [
    { month: "Jan", received: 42000, sent: 18000 },
    { month: "Feb", received: 51000, sent: 22000 },
    { month: "Mar", received: 47000, sent: 25000 },
    { month: "Apr", received: 69000, sent: 28000 },
    { month: "May", received: 61000, sent: 31000 },
    { month: "Jun", received: 74930, sent: 28440 }
];

const symbols = {
    PHP: "PHP ",
    USD: "$",
    EUR: "€",
    JPY: "¥",
    SGD: "S$"
};

function money(amount, code) {
    const symbol = symbols[code] || ${code} ;
    const formatted = Number(amount).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    return symbol.trim() === code ? ${symbol}${formatted} : ${symbol}${formatted} ${code};
}

function convert(amount, from, to) {
    const phpValue = Number(amount) * ratesToPhp[from];
    return phpValue / ratesToPhp[to];
}

function showToast(message) {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.classList.add("show");
    window.setTimeout(() => toast.classList.remove("show"), 2200);
}

function populateCurrencyOptions() {
    const selects = [
        "sendFrom",
        "sendTo",
        "requestCurrency",
        "exchangeFrom",
        "exchangeTo"
    ].map((id) => document.getElementById(id));

    selects.forEach((select) => {
        select.innerHTML = wallets
            .map((wallet) => <option value="${wallet.code}">${wallet.code} - ${wallet.name}</option>)
            .join("");
    });

    document.getElementById("sendTo").value = "PHP";
    document.getElementById("requestCurrency").value = "PHP";
    document.getElementById("exchangeFrom").value = "USD";
    document.getElementById("exchangeTo").value = "PHP";
}

function renderWallets() {
    const walletGrid = document.getElementById("walletGrid");
    const total = wallets.reduce((sum, wallet) => sum + wallet.balance * ratesToPhp[wallet.code], 0);

    document.getElementById("totalBalance").textContent = money(total, "PHP");
    walletGrid.innerHTML = wallets.map((wallet) => `
        <div class="wallet-card">
            <span class="badge ${wallet.accent}">${wallet.code}</span>
            <strong>${money(wallet.balance, wallet.code)}</strong>
            <small>${wallet.name} - 1 ${wallet.code} = PHP ${ratesToPhp[wallet.code].toFixed(2)}</small>
        </div>
    `).join("");
}

function renderActivity() {
    const activityList = document.getElementById("activityList");
    activityList.innerHTML = transactions.slice(0, 4).map((item) => `
        <div class="activity-row">
            <div>
                <strong>${item.type}</strong>
                <small>${item.ref} - ${item.user}</small>
            </div>
            <span class="badge ${statusClass(item.status)}">${item.status}</span>
        </div>
    `).join("");
}

function statusClass(status) {
    if (status === "completed") return "success";
    if (status === "pending") return "warning";
    if (status === "failed") return "danger";
    return "neutral";
}

function renderTransactions() {
    const table = document.getElementById("transactionTable");
    const search = document.getElementById("searchInput").value.toLowerCase();
    const status = document.getElementById("statusFilter").value;

    const rows = transactions.filter((item) => {
        const matchesSearch = ${item.ref} ${item.type} ${item.user} ${item.amount}.toLowerCase().includes(search);
        const matchesStatus = status === "all" || item.status === status;
        return matchesSearch && matchesStatus;
    });

    table.innerHTML = rows.map((item) => `
        <tr>
            <td><strong>${item.ref}</strong></td>
            <td>${item.type}</td>
            <td>${item.user}</td>
            <td>${item.amount}</td>
            <td><span class="badge ${statusClass(item.status)}">${item.status}</span></td>
            <td>${item.date}</td>
        </tr>
    ).join("") || `<tr><td colspan="6">No transactions found.</td></tr>;
}

function renderReport() {
    const chart = document.getElementById("reportChart");
    const max = Math.max(...monthlyReport.map((item) => Math.max(item.received, item.sent)));

    chart.innerHTML = monthlyReport.map((item) => `
        <div class="bar-row">
            <strong>${item.month}</strong>
            <div class="bar-track">
                <div class="bar in" style="width:${(item.received / max) * 100}%"></div>
                <div class="bar out" style="width:${(item.sent / max) * 100}%"></div>
            </div>
            <small>${money(item.received, "PHP")} in / ${money(item.sent, "PHP")} out</small>
        </div>
    `).join("");
}

function updateSendPreview() {
    const amount = document.getElementById("sendAmount").value || 0;
    const from = document.getElementById("sendFrom").value;
    const to = document.getElementById("sendTo").value;
    const converted = convert(amount, from, to);
    const phpValue = convert(amount, from, "PHP");

    document.getElementById("sendPreview").textContent = money(converted, to);
    document.getElementById("sendPhpValue").textContent = PHP base value: ${money(phpValue, "PHP")};
}

function updateExchangePreview() {
    const amount = document.getElementById("exchangeAmount").value || 0;
    const from = document.getElementById("exchangeFrom").value;
    const to = document.getElementById("exchangeTo").value;
    document.getElementById("exchangePreview").textContent = money(convert(amount, from, to), to);
}

function createReference() {
    const random = Math.random().toString(16).slice(2, 😎.toUpperCase();
    return REQ-260629-${random};
}

function bindEvents() {
    document.getElementById("menuButton").addEventListener("click", () => {
        document.getElementById("sidebar").classList.toggle("open");
    });

    document.querySelectorAll(".nav-link").forEach((link) => {
        link.addEventListener("click", () => {
            document.querySelectorAll(".nav-link").forEach((item) => item.classList.remove("active"));
            link.classList.add("active");
            document.getElementById("sidebar").classList.remove("open");
        });
    });

    document.querySelectorAll("[data-jump]").forEach((button) => {
        button.addEventListener("click", () => {
            document.querySelector(button.dataset.jump).scrollIntoView({ behavior: "smooth" });
        });
    });

    ["sendAmount", "sendFrom", "sendTo"].forEach((id) => {
        document.getElementById(id).addEventListener("input", updateSendPreview);
        document.getElementById(id).addEventListener("change", updateSendPreview);
    });

    ["exchangeAmount", "exchangeFrom", "exchangeTo"].forEach((id) => {
        document.getElementById(id).addEventListener("input", updateExchangePreview);
        document.getElementById(id).addEventListener("change", updateExchangePreview);
    });

    document.getElementById("searchInput").addEventListener("input", renderTransactions);
    document.getElementById("statusFilter").addEventListener("change", renderTransactions);
    document.getElementById("printButton").addEventListener("click", () => window.print());
    document.getElementById("logoutButton").addEventListener("click", () => showToast("Mock logout only. Backend session will be added later."));

    document.getElementById("sendForm").addEventListener("submit", (event) => {
        event.preventDefault();
        transactions.unshift({
            ref: SEND-260629-${Math.random().toString(16).slice(2, 5).toUpperCase()},
            type: "Send",
            user: "Juan Dela Cruz",
            amount: money(document.getElementById("sendAmount").value, document.getElementById("sendFrom").value),
            status: "completed",
            date: "Jun 29, 2026"
        });
        renderActivity();
        renderTransactions();
        showToast("Payment added to mock transaction history.");
    });

    document.getElementById("requestForm").addEventListener("submit", (event) => {
        event.preventDefault();
        const reference = createReference();
        document.getElementById("referenceCode").textContent = reference;
        document.getElementById("referenceStatus").textContent = "Status: Pending";
        showToast("Payment request reference generated.");
    });

    document.getElementById("exchangeForm").addEventListener("submit", (event) => {
        event.preventDefault();
        showToast("Currency exchange preview confirmed.");
    });

    document.getElementById("profileForm").addEventListener("submit", (event) => {
        event.preventDefault();
        showToast("Profile changes saved in this mock dashboard.");
    });
}

populateCurrencyOptions();
renderWallets();
renderActivity();
renderTransactions();
renderReport();
updateSendPreview();
updateExchangePreview();
bindEvents();
