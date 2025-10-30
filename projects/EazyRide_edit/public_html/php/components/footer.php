    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- About -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">VoltiaCar</h3>
                    <p class="text-gray-600 text-sm">
                        Servei de carsharing sostenible i accessible per a tothom.
                    </p>
                </div>
                
                <!-- Links -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Enllaços
                    </h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/pages/accessibility/accessibilitat.html" class="text-gray-600 hover:text-primary-green">
                            Accessibilitat
                        </a></li>
                        <li><a href="/pages/dashboard/resum-projecte.html" class="text-gray-600 hover:text-primary-green">
                            Sobre el Projecte
                        </a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Contacte
                    </h3>
                    <p class="text-gray-600 text-sm">
                        Email: info@voltiacar.cat<br>
                        Tel: +34 900 000 000
                    </p>
                </div>
            </div>
            
            <div class="border-t border-gray-200 mt-8 pt-8 text-center text-gray-600 text-sm">
                <p>&copy; <?php echo date('Y'); ?> VoltiaCar. Tots els drets reservats.</p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="/js/main.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- UserWay Accessibility Widget -->
    <script>
        (function(d){
            var s = d.createElement("script");
            s.setAttribute("data-account","RrwQjeYdrh");
            s.src = "https://cdn.userway.org/widget.js";
            (d.body || d.head).appendChild(s);
        })(document);
    </script>
</body>
</html>
