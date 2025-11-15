<?php
// includes/footer.php
?>
<style>
/* Ensure body and html have no margin/padding */
html, body {
    margin: 0;
    padding: 0;
    overflow-x: hidden; /* Prevent horizontal scroll */
}

/* FOOTER - Full width, edge to edge */
.footer {
    width: 100vw; /* Full viewport width */
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw; /* Pull it to screen edges */
    margin-right: -50vw;
    background: #000;
    color: #fff;
    padding: 40px 0;
    margin-top: 40px;
    box-sizing: border-box;
    text-align: center;
}

/* Inner container for content padding */
.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.footer h4 {
    margin: 0 0 20px 0;
    font-size: 1.2em;
}

.footer p {
    margin: 0 0 10px 0;
}

.footer .copyright {
    margin-top: 30px;
    opacity: 0.8;
}
</style>

<div class="footer">
    <div class="footer-content">
        <h4>Online 24/7 — Customer Support 1–5 PM Daily!</h4>
        <p>Address: 123 Camera Street, Taguig City, Metro Manila</p>
        <p>Email: support@lensify.ph</p>
        <p>Phone: +63 917 654 3210</p>
        <p class="copyright">© Lensify — 2025</p>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>


</body>
</html>