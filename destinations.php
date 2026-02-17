<?php include 'includes/header.php'; ?>

<section class="page-header"
    style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1502602898657-3e91760cbb34?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover; height: 300px; display: flex; align-items: center; justify-content: center; color: white; padding-top: 80px;">
    <div class="container text-center">
        <h1>Destinasyonlar</h1>
        <p>Dünyanın dört bir yanındaki eşsiz rotalar</p>
    </div>
</section>

<section class="destinations-page">
    <div class="container">
        <div class="destinations-grid"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <!-- Destination Card 1 -->
            <div class="destination-card">
                <img src="https://images.unsplash.com/photo-1516483638261-f4dbaf036963?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                    alt="İtalya">
                <div class="destination-overlay">
                    <h3>İtalya</h3>
                    <p>Roma, Venedik, Floransa ve daha fazlası.</p>
                    <a href="tours.php?destination=italy" class="btn-primary"
                        style="margin-top: 1rem; font-size: 0.9rem;">Turları Gör</a>
                </div>
            </div>

            <!-- Destination Card 2 -->
            <div class="destination-card">
                <img src="https://images.unsplash.com/photo-1527668752968-14dc70a27c95?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                    alt="Japonya">
                <div class="destination-overlay">
                    <h3>Japonya</h3>
                    <p>Tokyo, Kyoto, Osaka turları.</p>
                    <a href="tours.php?destination=japan" class="btn-primary"
                        style="margin-top: 1rem; font-size: 0.9rem;">Turları Gör</a>
                </div>
            </div>

            <!-- Destination Card 3 -->
            <div class="destination-card">
                <img src="https://images.unsplash.com/photo-1523531294919-4bcd7c65e216?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                    alt="İspanya">
                <div class="destination-overlay">
                    <h3>İspanya</h3>
                    <p>Barselona, Madrid, Endülüs turları.</p>
                    <a href="tours.php?destination=spain" class="btn-primary"
                        style="margin-top: 1rem; font-size: 0.9rem;">Turları Gör</a>
                </div>
            </div>

            <!-- Destination Card 4 -->
            <div class="destination-card">
                <img src="https://images.unsplash.com/photo-1589330273594-fade1ee91647?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                    alt="Mısır">
                <div class="destination-overlay">
                    <h3>Mısır</h3>
                    <p>Piramitler, Nil Nehri ve Sharm El Sheikh.</p>
                    <a href="tours.php?destination=egypt" class="btn-primary"
                        style="margin-top: 1rem; font-size: 0.9rem;">Turları Gör</a>
                </div>
            </div>

            <!-- Destination Card 5 -->
            <div class="destination-card">
                <img src="https://images.unsplash.com/photo-1506929562872-bb421503ef21?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                    alt="Tayland">
                <div class="destination-overlay">
                    <h3>Tayland</h3>
                    <p>Bangkok, Phuket, Pattaya turları.</p>
                    <a href="tours.php?destination=thailand" class="btn-primary"
                        style="margin-top: 1rem; font-size: 0.9rem;">Turları Gör</a>
                </div>
            </div>

            <!-- Destination Card 6 -->
            <div class="destination-card">
                <img src="https://images.unsplash.com/photo-1528702748617-c64d49f918af?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                    alt="Dubai">
                <div class="destination-overlay">
                    <h3>Dubai</h3>
                    <p>Lüks oteller, çöl safarisi ve alışveriş.</p>
                    <a href="tours.php?destination=dubai" class="btn-primary"
                        style="margin-top: 1rem; font-size: 0.9rem;">Turları Gör</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>