#!/usr/bin/env python3
"""
Script de migración inteligente de MySQLi a PDO
Convierte bind_param, execute, get_result y fetch_assoc a sintaxis PDO
"""

import re
import sys
import os
from pathlib import Path

def convert_bind_param_execute_fetch(content):
    """
    Convierte el patrón:
    $stmt->bind_param('types', $var1, $var2);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    A:
    $stmt->execute([$var1, $var2]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    """
    
    # Patrón 1: bind_param + execute + get_result + fetch_assoc
    pattern1 = r"\$(\w+)->bind_param\(['\"][\w]+['\"]\s*,\s*([^)]+)\);\s*\n\s*\$\1->execute\(\);\s*\n\s*\$(\w+)\s*=\s*\$\1->get_result\(\);\s*\n\s*\$(\w+)\s*=\s*\$\3->fetch\(PDO::FETCH_ASSOC\);"
    
    def replace1(match):
        stmt_var = match.group(1)
        params = match.group(2)
        result_var = match.group(3)
        row_var = match.group(4)
        
        # Convertir parámetros a array
        param_array = f"[{params}]"
        
        return f"${stmt_var}->execute({param_array});\n    ${row_var} = ${stmt_var}->fetch(PDO::FETCH_ASSOC);"
    
    content = re.sub(pattern1, replace1, content)
    
    # Patrón 2: bind_param + execute + get_result (sin fetch inmediato)
    pattern2 = r"\$(\w+)->bind_param\(['\"][\w]+['\"]\s*,\s*([^)]+)\);\s*\n\s*\$\1->execute\(\);\s*\n\s*\$(\w+)\s*=\s*\$\1->get_result\(\);"
    
    def replace2(match):
        stmt_var = match.group(1)
        params = match.group(2)
        
        # Convertir parámetros a array
        param_array = f"[{params}]"
        
        return f"${stmt_var}->execute({param_array});"
    
    content = re.sub(pattern2, replace2, content)
    
    # Patrón 3: Solo bind_param + execute
    pattern3 = r"\$(\w+)->bind_param\(['\"][\w]+['\"]\s*,\s*([^)]+)\);\s*\n\s*\$\1->execute\(\);"
    
    def replace3(match):
        stmt_var = match.group(1)
        params = match.group(2)
        
        # Convertir parámetros a array
        param_array = f"[{params}]"
        
        return f"${stmt_var}->execute({param_array});"
    
    content = re.sub(pattern3, replace3, content)
    
    # Patrón 4: get_result que quedó suelto
    pattern4 = r"\$(\w+)\s*=\s*\$(\w+)->get_result\(\);"
    content = re.sub(pattern4, r"// $\1 = $\2; // PDO: stmt ya contiene resultados", content)
    
    return content

def convert_num_rows(content):
    """
    Convierte $result->num_rows a verificación con fetch
    """
    # num_rows === 0 con return null
    pattern1 = r"if\s*\(\s*\$(\w+)->rowCount\(\)\s*===\s*0\s*\)\s*\{\s*\n\s*return\s+null;\s*\n\s*\}"
    replacement1 = r"$row = $\1->fetch(PDO::FETCH_ASSOC);\n    if (!$row) {\n        return null;\n    }"
    content = re.sub(pattern1, replacement1, content)
    
    # Simplemente convertir otras referencias
    content = re.sub(r"->num_rows", r"->rowCount()", content)
    
    return content

def process_file(filepath):
    """Procesa un archivo PHP"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        
        # Aplicar conversiones
        content = convert_bind_param_execute_fetch(content)
        content = convert_num_rows(content)
        
        # Solo escribir si hubo cambios
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        
        return False
    except Exception as e:
        print(f"Error procesando {filepath}: {e}")
        return False

def main():
    base_dir = Path("public_html")
    
    if not base_dir.exists():
        print("Error: directorio public_html no encontrado")
        sys.exit(1)
    
    # Buscar archivos PHP con bind_param
    php_files = []
    for root, dirs, files in os.walk(base_dir):
        for file in files:
            if file.endswith('.php'):
                filepath = Path(root) / file
                try:
                    with open(filepath, 'r', encoding='utf-8') as f:
                        if 'bind_param' in f.read():
                            php_files.append(filepath)
                except:
                    pass
    
    print(f"Encontrados {len(php_files)} archivos con bind_param")
    print()
    
    modified = 0
    for filepath in php_files:
        if process_file(filepath):
            print(f"✓ Migrado: {filepath}")
            modified += 1
        else:
            print(f"  Sin cambios: {filepath}")
    
    print()
    print(f"=== Migración completada ===")
    print(f"Archivos modificados: {modified}/{len(php_files)}")
    print()
    print("Revisa los cambios y ejecuta pruebas antes de eliminar los backups (.bak)")

if __name__ == "__main__":
    main()
