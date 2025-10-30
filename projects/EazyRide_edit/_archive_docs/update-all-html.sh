#!/bin/bash

# Script para actualizar todos los HTML con header, footer y traducciones

PUBLIC_HTML="/Users/ganso/Desktop/EazyRide_edit/public_html"

echo "🔧 Actualizando todos los archivos HTML..."

# Función para obtener la ruta relativa correcta
get_relative_path() {
    local file=$1
    local depth=$(echo "$file" | grep -o "/" | wc -l)
    local base_depth=$(echo "$PUBLIC_HTML" | grep -o "/" | wc -l)
    local rel_depth=$((depth - base_depth - 1))
    
    local prefix=""
    for ((i=0; i<rel_depth; i++)); do
        prefix="../$prefix"
    done
    echo "$prefix"
}

# Encontrar todos los HTML excepto componentes
find "$PUBLIC_HTML" -name "*.html" ! -path "*/components/*" | while read file; do
    echo "📄 Procesando: $(basename $file)"
    
    # Calcular ruta relativa
    rel=$(get_relative_path "$file")
    
    # Crear backup
    cp "$file" "$file.backup"
    
    # Extraer contenido entre <body> y </body>
    body_content=$(sed -n '/<body[^>]*>/,/<\/body>/p' "$file" | sed '1d;$d')
    
    # Crear nuevo HTML
    cat > "$file" << HTML_START
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EazyRide</title>
    <link rel="stylesheet" href="${rel}css/main.css">
    <script src="${rel}js/translations.js" defer></script>
    <script src="${rel}js/toast.js" defer></script>
</head>
<body>
    <!-- Header -->
    <div id="header-placeholder"></div>
    
    <!-- Main Content -->
    <main class="main-content">
$body_content
    </main>
    
    <!-- Footer -->
    <div id="footer-placeholder"></div>
    
    <!-- Load Header & Footer -->
    <script>
        // Load header
        fetch('${rel}components/header.html')
            .then(response => response.text())
            .then(data => {
                document.getElementById('header-placeholder').innerHTML = data;
            });
        
        // Load footer
        fetch('${rel}components/footer.html')
            .then(response => response.text())
            .then(data => {
                document.getElementById('footer-placeholder').innerHTML = data;
            });
    </script>
</body>
</html>
HTML_START

done

echo "✅ Actualización completada"
echo "📝 Los backups están guardados como .backup"
