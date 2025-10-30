#!/bin/bash
# Fix remaining bind_param instances

files=(
    "public_html/php/admin/users.php"
    "public_html/php/admin/bookings.php"
    "public_html/php/user/update-profile.php"
    "public_html/php/user/change-password.php"
    "public_html/php/api/search-vehicles.php"
    "public_html/php/api/book-vehicle.php"
    "public_html/php/api/subscribe-premium.php"
    "public_html/php/api/purchase-time.php"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "Processing $file..."
        # Crear backup si no existe
        [ ! -f "${file}.bak2" ] && cp "$file" "${file}.bak2"
        
        # Usar perl para hacer reemplazos multi-línea más complejos
        perl -i -pe 's/\$(\w+)->bind_param\([^)]+\);\s*\n\s*\$\1->execute\(\);/\$\1->execute();/g' "$file"
        perl -i -pe 's/\$(\w+)->bind_param\([^)]+\);/\/\/ bind_param removed - use array params in execute()/g' "$file"
    fi
done

echo "Done!"
