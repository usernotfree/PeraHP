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

function byId(id) {
    return document.getElementById(id);
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (character) {
        return {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            "\"": "&quot;",
            "'": "&#039;"
        }[character];
    });
}

function money(amount, code) {
    const numericAmount = Number(amount) || 0;
    const formatted = numericAmount.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    return code + " " + formatted;
}

function phpValue(amount, code) {
    return (Number(amount) || 0) * (ratesToPhp[code] || 1);
}

function convert(amount, from, to) {
    return phpValue(amount, from) / (ratesToPhp[to] || 1);
}

function findWallet(code) {
    return wallets.find(function (wallet) {
        return wallet.code === code;
    });
}

function setText(id, value) {
    const element = byId(id);
    if (element) {
        element.textContent = value;
    }
}

function statusClass(status) {
    if (status === "completed") return "success";
    if (status === "pending") return "warning";
    if (status === "failed") return "danger";
    return "neutral";
}

function statusLabel(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function todayLabel() {
    return new Intl.DateTimeFormat("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric"
    }).format(new Date());
}

function referenceStamp() {
    const now = new Date();
    const year = String(now.getFullYear()).slice(-2);
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");
    return year + month + day;
}

function createReference(prefix) {
    const random = Math.random().toString(16).slice(2, 5).toUpperCase();
    return prefix + "-" + referenceStamp() + "-" + random;
}

function showToast(message) {
    const toast = byId("toast");
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add("show");
    window.setTimeout(function () {
        toast.classList.remove("show");
    }, 2200);
}

function populateCurrencyOptions() {
    const selectIds = ["sendFrom", "sendTo", "requestCurrency", "exchangeFrom", "exchangeTo"];

    selectIds.forEach(function (id) {
        const select = byId(id);
        if (!select) return;
        select.innerHTML = wallets.map(function (wallet) {
            return "<option value=\"" + wallet.code + "\">" + wallet.code + " - " + wallet.name + "</option>";
        }).join("");
    });

    if (byId("sendFrom")) byId("sendFrom").value = "USD";
    if (byId("sendTo")) byId("sendTo").value = "PHP";
    if (byId("requestCurrency")) byId("requestCurrency").value = "PHP";
    if (byId("exchangeFrom")) byId("exchangeFrom").value = "USD";
    if (byId("exchangeTo")) byId("exchangeTo").value = "PHP";
}

function renderMetrics() {
    const total = wallets.reduce(function (sum, wallet) {
        return sum + phpValue(wallet.balance, wallet.code);
    }, 0);
    const currentMonth = monthlyReport[monthlyReport.length - 1];
    const pendingRequests = transactions.filter(function (item) {
        return item.type === "Request" && item.status === "pending";
    }).length;

    setText("totalBalance", money(total, "PHP"));
    setText("monthlyReceived", money(currentMonth.received, "PHP"));
    setText("monthlySent", money(currentMonth.sent, "PHP"));
    setText("pendingCount", String(pendingRequests));
}

function renderWallets() {
    const walletGrid = byId("walletGrid");
    if (!walletGrid) return;

    walletGrid.innerHTML = wallets.map(function (wallet) {
        return "<div class=\"wallet-card\">" +
            "<div class=\"wallet-top\"><span class=\"badge " + wallet.accent + "\">" + wallet.code + "</span><strong>" + money(wallet.balance, wallet.code) + "</strong></div>" +
            "<small>" + escapeHtml(wallet.name) + " - 1 " + wallet.code + " = PHP " + ratesToPhp[wallet.code].toFixed(2) + "</small>" +
            "<small>PHP value: " + money(phpValue(wallet.balance, wallet.code), "PHP") + "</small>" +
            "</div>";
    }).join("");
}

function renderRates() {
    const rateGrid = byId("rateGrid");
    if (!rateGrid) return;

    rateGrid.innerHTML = wallets.map(function (wallet) {
        return "<article class=\"rate-card\"><span>" + wallet.code + "</span><strong>1 " + wallet.code + " = PHP " + ratesToPhp[wallet.code].toFixed(2) + "</strong><small>Used for wallet totals, transfers, exchanges, and reports.</small></article>";
    }).join("");
}

function renderActivity() {
    const activityList = byId("activityList");
    if (!activityList) return;

    activityList.innerHTML = transactions.slice(0, 5).map(function (item) {
        return "<div class=\"activity-row\">" +
            "<div><strong>" + escapeHtml(item.type) + "</strong><small>" + escapeHtml(item.ref) + " - " + escapeHtml(item.user) + "</small></div>" +
            "<div><strong class=\"activity-amount\">" + money(item.amount, item.currency) + "</strong><span class=\"badge " + statusClass(item.status) + "\">" + statusLabel(item.status) + "</span></div>" +
            "</div>";
    }).join("");
}

function renderTransactions() {
    const table = byId("transactionTable");
    const searchInput = byId("searchInput");
    const statusFilter = byId("statusFilter");
    if (!table) return;

    const search = searchInput ? searchInput.value.toLowerCase() : "";
    const status = statusFilter ? statusFilter.value : "all";

    const rows = transactions.filter(function (item) {
        const amountText = money(item.amount, item.currency);
        const haystack = (item.ref + " " + item.type + " " + item.user + " " + amountText + " " + item.status).toLowerCase();
        const matchesSearch = haystack.includes(search);
        const matchesStatus = status === "all" || item.status === status;
        return matchesSearch && matchesStatus;
    });

    table.innerHTML = rows.map(function (item) {
        return "<tr>" +
            "<td><strong>" + escapeHtml(item.ref) + "</strong></td>" +
            "<td>" + escapeHtml(item.type) + "</td>" +
            "<td>" + escapeHtml(item.user) + "</td>" +
            "<td>" + money(item.amount, item.currency) + "</td>" +
            "<td><span class=\"badge " + statusClass(item.status) + "\">" + statusLabel(item.status) + "</span></td>" +
            "<td>" + escapeHtml(item.date) + "</td>" +
            "</tr>";
    }).join("") || "<tr><td colspan=\"6\">No transactions found.</td></tr>";
}

function renderReport() {
    const chart = byId("reportChart");
    if (!chart) return;

    const max = Math.max.apply(null, monthlyReport.map(function (item) {
        return Math.max(item.received, item.sent);
    }));

    chart.innerHTML = monthlyReport.map(function (item) {
        const receivedWidth = Math.max(3, (item.received / max) * 100);
        const sentWidth = Math.max(3, (item.sent / max) * 100);
        return "<div class=\"bar-row\">" +
            "<strong>" + item.month + "</strong>" +
            "<div class=\"bar-track\"><div class=\"bar in\" style=\"width:" + receivedWidth + "%\"></div><div class=\"bar out\" style=\"width:" + sentWidth + "%\"></div></div>" +
            "<small>" + money(item.received, "PHP") + " in / " + money(item.sent, "PHP") + " out</small>" +
            "</div>";
    }).join("");
}

function updateSendPreview() {
    const amountElement = byId("sendAmount");
    const fromElement = byId("sendFrom");
    const toElement = byId("sendTo");
    if (!amountElement || !fromElement || !toElement) return;

    const amount = Number(amountElement.value) || 0;
    const from = fromElement.value;
    const to = toElement.value;
    const converted = convert(amount, from, to);
    const baseValue = phpValue(amount, from);

    setText("sendPreview", money(converted, to));
    setText("sendPhpValue", "PHP base value: " + money(baseValue, "PHP"));
}

function updateExchangePreview() {
    const amountElement = byId("exchangeAmount");
    const fromElement = byId("exchangeFrom");
    const toElement = byId("exchangeTo");
    if (!amountElement || !fromElement || !toElement) return;

    const amount = Number(amountElement.value) || 0;
    const from = fromElement.value;
    const to = toElement.value;
    setText("exchangePreview", money(convert(amount, from, to), to));
}

function renderAll() {
    renderMetrics();
    renderWallets();
    renderRates();
    renderActivity();
    renderTransactions();
    renderReport();
}

function bindEvents() {
    const menuButton = byId("menuButton");
    const sidebar = byId("sidebar");
    if (menuButton && sidebar) {
        menuButton.addEventListener("click", function () {
            sidebar.classList.toggle("open");
        });
    }

    document.querySelectorAll(".nav-link").forEach(function (link) {
        link.addEventListener("click", function () {
            document.querySelectorAll(".nav-link").forEach(function (item) {
                item.classList.remove("active");
            });
            link.classList.add("active");
            if (sidebar) sidebar.classList.remove("open");
        });
    });

    document.querySelectorAll("[data-jump]").forEach(function (button) {
        button.addEventListener("click", function () {
            const target = document.querySelector(button.dataset.jump);
            if (target) target.scrollIntoView({ behavior: "smooth", block: "start" });
        });
    });

    ["sendAmount", "sendFrom", "sendTo"].forEach(function (id) {
        const element = byId(id);
        if (!element) return;
        element.addEventListener("input", updateSendPreview);
        element.addEventListener("change", updateSendPreview);
    });

    ["exchangeAmount", "exchangeFrom", "exchangeTo"].forEach(function (id) {
        const element = byId(id);
        if (!element) return;
        element.addEventListener("input", updateExchangePreview);
        element.addEventListener("change", updateExchangePreview);
    });

    const searchInput = byId("searchInput");
    const statusFilter = byId("statusFilter");
    if (searchInput) searchInput.addEventListener("input", renderTransactions);
    if (statusFilter) statusFilter.addEventListener("change", renderTransactions);

    const printButton = byId("printButton");
    if (printButton) printButton.addEventListener("click", function () { window.print(); });

    const sendForm = byId("sendForm");
    if (sendForm) {
        sendForm.addEventListener("submit", function (event) {
            event.preventDefault();
            const amount = Number(byId("sendAmount").value) || 0;
            const from = byId("sendFrom").value;
            const wallet = findWallet(from);
            const recipient = byId("recipientEmail") && byId("recipientEmail").value ? byId("recipientEmail").value : "Recipient";

            if (amount <= 0) {
                showToast("Enter an amount greater than zero.");
                return;
            }
            if (!wallet || wallet.balance < amount) {
                showToast("Insufficient wallet balance for this transfer.");
                return;
            }

            wallet.balance -= amount;
            transactions.unshift({
                ref: createReference("SEND"),
                type: "Send",
                user: recipient,
                amount: amount,
                currency: from,
                status: "completed",
                date: todayLabel()
            });
            renderAll();
            updateSendPreview();
            showToast("Payment sent and added to transaction history.");
        });
    }

    const requestForm = byId("requestForm");
    if (requestForm) {
        requestForm.addEventListener("submit", function (event) {
            event.preventDefault();
            const amount = Number(byId("requestAmount").value) || 0;
            const currency = byId("requestCurrency").value;
            const payer = byId("payerEmail") && byId("payerEmail").value ? byId("payerEmail").value : "Payer";

            if (amount <= 0) {
                showToast("Enter a request amount greater than zero.");
                return;
            }

            const reference = createReference("REQ");
            transactions.unshift({
                ref: reference,
                type: "Request",
                user: payer,
                amount: amount,
                currency: currency,
                status: "pending",
                date: todayLabel()
            });
            setText("referenceCode", reference);
            setText("referenceStatus", "Status: Pending");
            renderAll();
            showToast("Payment request reference generated.");
        });
    }

    const exchangeForm = byId("exchangeForm");
    if (exchangeForm) {
        exchangeForm.addEventListener("submit", function (event) {
            event.preventDefault();
            const amount = Number(byId("exchangeAmount").value) || 0;
            const from = byId("exchangeFrom").value;
            const to = byId("exchangeTo").value;
            const fromWallet = findWallet(from);
            const toWallet = findWallet(to);

            if (amount <= 0) {
                showToast("Enter an exchange amount greater than zero.");
                return;
            }
            if (from === to) {
                showToast("Choose two different currencies for exchange.");
                return;
            }
            if (!fromWallet || !toWallet || fromWallet.balance < amount) {
                showToast("Insufficient wallet balance for this exchange.");
                return;
            }

            const convertedAmount = convert(amount, from, to);
            fromWallet.balance -= amount;
            toWallet.balance += convertedAmount;
            transactions.unshift({
                ref: createReference("EXCH"),
                type: "Exchange",
                user: "Wallet exchange",
                amount: amount,
                currency: from,
                status: "completed",
                date: todayLabel()
            });
            renderAll();
            updateExchangePreview();
            updateSendPreview();
            showToast("Currency exchange completed in the mock wallet.");
        });
    }

    const profileForm = byId("profileForm");
    if (profileForm) {
        profileForm.addEventListener("submit", function (event) {
            event.preventDefault();
            showToast("Profile changes saved for this mock account.");
        });
    }

    const settingsProfileForm = byId("settingsProfileForm");
    if (settingsProfileForm) {
        settingsProfileForm.addEventListener("submit", function (event) {
            event.preventDefault();
            showToast("Profile settings saved for this mock account.");
        });
    }

    const settingsSecurityForm = byId("settingsSecurityForm");
    if (settingsSecurityForm) {
        settingsSecurityForm.addEventListener("submit", function (event) {
            event.preventDefault();
            const newPassword = byId("newPassword") ? byId("newPassword").value : "";
            const confirmPassword = byId("confirmPassword") ? byId("confirmPassword").value : "";

            if (newPassword !== confirmPassword) {
                showToast("New password and confirmation must match.");
                return;
            }

            showToast("Security settings saved in this mock page.");
        });
    }

    const settingsPreferenceForm = byId("settingsPreferenceForm");
    if (settingsPreferenceForm) {
        settingsPreferenceForm.addEventListener("submit", function (event) {
            event.preventDefault();
            const dailyLimit = Number(byId("dailyLimit") ? byId("dailyLimit").value : 0) || 0;
            const transactionLimit = Number(byId("transactionLimit") ? byId("transactionLimit").value : 0) || 0;

            if (transactionLimit > dailyLimit) {
                showToast("Single transaction limit cannot exceed the daily limit.");
                return;
            }

            showToast("Wallet preferences saved.");
        });
    }

    const settingsNotificationForm = byId("settingsNotificationForm");
    if (settingsNotificationForm) {
        settingsNotificationForm.addEventListener("submit", function (event) {
            event.preventDefault();
            showToast("Notification settings saved.");
        });
    }

    document.querySelectorAll("[data-settings-action]").forEach(function (button) {
        button.addEventListener("click", function () {
            const action = button.dataset.settingsAction;
            if (action === "download") showToast("Data export request queued.");
            if (action === "freeze") showToast("Wallet freeze review started.");
            if (action === "close") showToast("Account closure review started.");
            if (action === "report") showToast("Report view updated for this mock page.");
        });
    });
}

function initializeDashboard() {
    populateCurrencyOptions();
    renderAll();
    updateSendPreview();
    updateExchangePreview();
    bindEvents();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeDashboard);
} else {
    initializeDashboard();
}
