<?php
require_once __DIR__ . "/auth.php";
$loggedIn = is_logged_in();
$user = $loggedIn ? current_user() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Discover PeraHP wallet rewards, payment promos, and everyday money offers.">
    <title>PeraHP - Promos that move with you</title>
    <link rel="stylesheet" href="styles.css?v=20260712-home">
</head>
<body class="home-page">
    <header class="home-header">
        <a class="home-brand" href="index.php" aria-label="PeraHP home">
            <img src="logo.png" alt="PeraHP logo">
            <span><strong>PeraHP</strong><small>Your everyday wallet</small></span>
        </a>
        <nav class="home-nav" aria-label="Main navigation">
            <a href="#promos">Promos</a>
            <a href="#perks">Perks</a>
            <a href="#about">Why PeraHP</a>
        </nav>
        <div class="home-account">
            <?php if ($loggedIn): ?>
                <span class="home-user">Hi, <?php echo e(explode(" ", trim($user["name"]))[0]); ?></span>
                <a class="home-login" href="main.php">Open dashboard</a>
            <?php else: ?>
                <a class="home-login secondary" href="register.php">Create account</a>
                <a class="home-login" href="login.php">Log in</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <section class="home-hero">
            <div class="home-hero-copy">
                <p class="home-kicker">Money feels better with rewards</p>
                <h1>More ways to pay.<br><em>More reasons to smile.</em></h1>
                <p>Discover colorful deals, wallet perks, and easy everyday payments—all in one secure PeraHP account.</p>
                <div class="home-hero-actions">
                    <a class="home-cta" href="<?php echo $loggedIn ? 'main.php' : 'register.php'; ?>"><?php echo $loggedIn ? 'Go to my wallet' : 'Get started free'; ?></a>
                    <a class="home-text-link" href="#promos">See today's offers <span>↓</span></a>
                </div>
            </div>
            <div class="hero-offer-card" aria-label="Featured promotion">
                <span class="offer-pill">Featured offer</span>
                <div class="offer-orb">15%</div>
                <h2>Make every payment count.</h2>
                <p>Earn up to 15% bonus rewards when you use your PeraHP wallet this month.</p>
                <small>Terms and conditions apply.</small>
            </div>
        </section>

        <section class="home-trust" aria-label="PeraHP highlights">
            <span><strong>Fast</strong> instant transfers</span>
            <span><strong>Secure</strong> protected payments</span>
            <span><strong>Flexible</strong> multi-currency wallets</span>
            <span><strong>Rewarding</strong> everyday promos</span>
        </section>

        <section class="promo-section" id="promos">
            <div class="home-section-heading">
                <div><p class="home-kicker">What's new</p><h2>Deals worth opening your wallet for.</h2></div>
                <p>Fresh ways to save, earn, and enjoy more from your money.</p>
            </div>
            <div class="promo-grid">
                <article class="promo-card promo-lilac">
                    <span class="promo-tag">New users</span><div class="promo-icon">₱</div>
                    <h3>Welcome bonus unlocked</h3><p>Complete your first cash-in and receive a surprise wallet reward.</p>
                    <a href="<?php echo $loggedIn ? 'wallets.php' : 'register.php'; ?>">Claim the offer <span>→</span></a>
                </article>
                <article class="promo-card promo-mint">
                    <span class="promo-tag">Transfers</span><div class="promo-icon">↗</div>
                    <h3>Send more, pay less</h3><p>Enjoy your first eligible wallet transfer with no service fee.</p>
                    <a href="<?php echo $loggedIn ? 'wallets.php' : 'login.php'; ?>">Send money <span>→</span></a>
                </article>
                <article class="promo-card promo-peach">
                    <span class="promo-tag">Cashback</span><div class="promo-icon">%</div>
                    <h3>Weekend cashback</h3><p>Pay with PeraHP on weekends and get a little something back.</p>
                    <a href="<?php echo $loggedIn ? 'transactions.php' : 'register.php'; ?>">Learn more <span>→</span></a>
                </article>
            </div>
        </section>

        <section class="perks-section" id="perks">
            <div class="perks-copy"><p class="home-kicker">Made for real life</p><h2>Your money, ready when you are.</h2><p>Move between currencies, request payments, track spending, and stay on top of every transaction without the clutter.</p><a class="home-cta dark" href="<?php echo $loggedIn ? 'main.php' : 'login.php'; ?>">Explore your wallet</a></div>
            <div class="perks-list">
                <article><span>01</span><div><h3>One clear balance</h3><p>See the PHP value of every wallet at a glance.</p></div></article>
                <article><span>02</span><div><h3>Easy exchanges</h3><p>Move money between supported currencies smoothly.</p></div></article>
                <article><span>03</span><div><h3>Activity that makes sense</h3><p>Understand what came in, what went out, and what's pending.</p></div></article>
            </div>
        </section>

        <section class="home-final" id="about">
            <img src="logo.png" alt="" aria-hidden="true">
            <div><p class="home-kicker">Start with PeraHP</p><h2>A friendlier way to manage everyday money.</h2></div>
            <a class="home-cta" href="<?php echo $loggedIn ? 'main.php' : 'register.php'; ?>"><?php echo $loggedIn ? 'Open dashboard' : 'Create my account'; ?></a>
        </section>
    </main>

    <footer class="home-footer"><a class="home-brand footer-brand" href="index.php"><img src="logo.png" alt=""><span><strong>PeraHP</strong><small>Your everyday wallet</small></span></a><p>© <?php echo date('Y'); ?> PeraHP. Move money with confidence.</p></footer>
    <button class="assistant-launcher" id="assistantLauncher" type="button" aria-label="Open PeraHP Assist" aria-expanded="false">
        <span class="assistant-launcher-mark" aria-hidden="true"><img src="logo.png" alt=""></span>
        <span class="assistant-launcher-copy"><small>Need help?</small><strong>Ask PeraHP</strong></span>
        <i aria-hidden="true"></i>
    </button>
    <aside class="assistant-window" id="assistantWindow" aria-label="PeraHP Assist" aria-hidden="true">
        <header><div><span class="assistant-avatar"><img src="logo.png" alt=""></span><div><small class="assistant-eyebrow">Your wallet guide</small><strong>PeraHP Assist <em>Beta</em></strong><small class="assistant-status"><i></i> Online</small></div></div><button type="button" id="assistantClose" aria-label="Close assistant">×</button></header>
        <div class="assistant-messages" id="assistantMessages" aria-live="polite">
            <div class="assistant-welcome"><span>Smart wallet support</span><strong>How can I help?</strong><small>Ask about your PeraHP experience anytime.</small></div>
            <div class="assistant-message bot"><p>Hi! I’m PeraHP Assist. Ask me about wallets, transfers, exchange, security, or getting started.</p></div>
        </div>
        <div class="assistant-suggestions" id="assistantSuggestions"></div>
        <form class="assistant-form" id="assistantForm">
            <label class="sr-only" for="assistantInput">Ask PeraHP Assist</label>
            <input id="assistantInput" type="text" maxlength="500" placeholder="Ask a question…" autocomplete="off" required>
            <button type="submit" aria-label="Send message"><span>↑</span></button>
        </form>
        <small class="assistant-disclaimer">PeraHP Assist provides general product guidance.</small>
    </aside>
    <script>window.PERAHP_ASSISTANT_TOKEN = <?php echo json_encode(csrf_token()); ?>;</script>
    <script src="script.js"></script>
</body>
</html>

