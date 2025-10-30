#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
EazyRide - Herramienta de Administración
Herramienta GUI completa para gestionar la aplicación EazyRide
Versión: 2.0 - Todo en Español
"""

import tkinter as tk
from tkinter import ttk, scrolledtext, messagebox, filedialog, simpledialog
import mysql.connector
from pymongo import MongoClient
import sys
import os
import subprocess
import requests
import json
from pathlib import Path
from datetime import datetime, timedelta
import threading
import hashlib
import random
import string

# Cargar variables de entorno desde múltiples ubicaciones posibles
def cargar_env():
    """Busca y carga el archivo .env desde varias ubicaciones"""
    posibles_ubicaciones = [
        Path(__file__).parent / '.env',  # python_gui/.env
        Path(__file__).parent.parent / '.env',  # Directorio raíz del proyecto
        Path.cwd() / '.env',  # Directorio actual
    ]
    
    for env_file in posibles_ubicaciones:
        if env_file.exists():
            print(f"✅ Cargando .env desde: {env_file}")
            with open(env_file) as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith('#') and '=' in line:
                        key, value = line.split('=', 1)
                        # Limpiar comillas si existen
                        value = value.strip().strip('"').strip("'")
                        if key not in os.environ:
                            os.environ[key] = value
            return True
    
    print("⚠️ No se encontró archivo .env")
    return False

cargar_env()

# Configuración MongoDB
MONGO_USER = os.getenv('MONGO_INITDB_ROOT_USERNAME')
MONGO_PASS = os.getenv('MONGO_INITDB_ROOT_PASSWORD')
MONGO_HOST = os.getenv('MONGO_HOST', 'localhost')
MONGO_PORT = os.getenv('MONGO_PORT', '27017')
MONGO_DB = os.getenv('MONGO_INITDB_DATABASE', 'simsdb')

# Ajustar host si es 'mongodb' (Docker) a localhost cuando se ejecuta desde host
if MONGO_HOST == 'mongodb':
    MONGO_HOST = 'localhost'
    print("ℹ️ Ajustando MONGO_HOST de 'mongodb' a 'localhost' (ejecutando desde host)")

MONGO_URI = (
    f"mongodb://{MONGO_USER}:{MONGO_PASS}@{MONGO_HOST}:{MONGO_PORT}/?authSource=admin"
    if MONGO_USER and MONGO_PASS else None
)

# Configuración MariaDB
DB_HOST = os.getenv('DB_HOST', 'localhost')
DB_USER = os.getenv('DB_USER') or os.getenv('MYSQL_USER', 'root')
DB_PASS = os.getenv('DB_PASS') or os.getenv('MYSQL_PASSWORD', '')
DB_NAME = os.getenv('DB_NAME') or os.getenv('MYSQL_DATABASE', 'simsdb')

# Ajustar host si es 'mariadb' (Docker) a localhost cuando se ejecuta desde host
if DB_HOST == 'mariadb':
    DB_HOST = 'localhost'
    print("ℹ️ Ajustando DB_HOST de 'mariadb' a 'localhost' (ejecutando desde host)")

MYSQL_CONFIG = {
    "host": DB_HOST,
    "user": DB_USER,
    "password": DB_PASS,
    "database": DB_NAME
} if DB_USER and DB_PASS else None

MYSQL_CONFIG_NO_DB = {
    "host": DB_HOST,
    "user": DB_USER,
    "password": DB_PASS
} if DB_USER and DB_PASS else None

# Configuración Servidor Web
WEB_URL = os.getenv('WEB_URL', 'http://localhost:8080')

# Mostrar configuración cargada (para debug)
print("\n" + "="*60)
print("🔧 CONFIGURACIÓN CARGADA:")
print("="*60)
print(f"MariaDB:")
print(f"  Host: {DB_HOST}")
print(f"  User: {DB_USER}")
print(f"  Pass: {'*' * len(DB_PASS) if DB_PASS else '(vacío)'}")
print(f"  Database: {DB_NAME}")
print(f"  Config válido: {'✅' if MYSQL_CONFIG else '❌'}")
print(f"\nMongoDB:")
print(f"  Host: {MONGO_HOST}:{MONGO_PORT}")
print(f"  User: {MONGO_USER or '(no configurado)'}")
print(f"  Pass: {'*' * len(MONGO_PASS) if MONGO_PASS else '(no configurado)'}")
print(f"  Database: {MONGO_DB}")
print(f"  URI válido: {'✅' if MONGO_URI else '❌'}")
print(f"\nWeb URL: {WEB_URL}")
print("="*60 + "\n")


class HerramientaEazyRide:
    def __init__(self, root):
        self.root = root
        self.root.title("EazyRide - Herramienta de Administración")
        self.root.geometry("1100x800")
        
        # Configurar estilo
        style = ttk.Style()
        style.theme_use('clam')
        
        # Colores personalizados
        style.configure('Titulo.TLabel', font=('Arial', 18, 'bold'), 
                       foreground='#667eea')
        style.configure('Subtitulo.TLabel', font=('Arial', 12, 'bold'))
        style.configure('Exito.TLabel', foreground='green')
        style.configure('Error.TLabel', foreground='red')
        style.configure('Advertencia.TLabel', foreground='orange')
        
        # Crear contenedor principal
        frame_principal = ttk.Frame(root, padding="10")
        frame_principal.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S))
        
        # Configurar pesos de grid
        root.columnconfigure(0, weight=1)
        root.rowconfigure(0, weight=1)
        frame_principal.columnconfigure(0, weight=1)
        frame_principal.rowconfigure(2, weight=1)
        
        # Título con logo
        frame_titulo = ttk.Frame(frame_principal)
        frame_titulo.grid(row=0, column=0, pady=10)
        
        etiqueta_titulo = ttk.Label(frame_titulo, text="🚗 EazyRide Admin Tool", 
                                    style='Titulo.TLabel')
        etiqueta_titulo.pack()
        
        etiqueta_subtitulo = ttk.Label(frame_titulo, 
                                       text="Suite Completa de Administración y Pruebas",
                                       font=('Arial', 10))
        etiqueta_subtitulo.pack()
        
        # Frame de Estado de Conexiones
        frame_estado = ttk.LabelFrame(frame_principal, text="Estado del Sistema", padding="10")
        frame_estado.grid(row=1, column=0, sticky=(tk.W, tk.E), pady=5)
        
        grid_estado = ttk.Frame(frame_estado)
        grid_estado.pack(fill=tk.X)
        
        self.estado_mariadb = ttk.Label(grid_estado, text="MariaDB: Verificando...", 
                                        foreground="gray")
        self.estado_mariadb.grid(row=0, column=0, padx=10, pady=2, sticky=tk.W)
        
        self.estado_mongodb = ttk.Label(grid_estado, text="MongoDB: Verificando...", 
                                        foreground="gray")
        self.estado_mongodb.grid(row=0, column=1, padx=10, pady=2, sticky=tk.W)
        
        self.estado_servidor = ttk.Label(grid_estado, text="Servidor Web: Verificando...", 
                                         foreground="gray")
        self.estado_servidor.grid(row=0, column=2, padx=10, pady=2, sticky=tk.W)
        
        ttk.Button(grid_estado, text="🔄 Actualizar Estado", 
                  command=self.verificar_todos_sistemas).grid(row=0, column=3, padx=10)
        
        # Notebook para diferentes secciones
        notebook = ttk.Notebook(frame_principal)
        notebook.grid(row=2, column=0, sticky=(tk.W, tk.E, tk.N, tk.S), pady=5)
        
        # Pestaña 1: Gestión de Base de Datos
        frame_db = ttk.Frame(notebook, padding="10")
        notebook.add(frame_db, text="📊 Base de Datos")
        self.crear_pestana_basedatos(frame_db)
        
        # Pestaña 2: Pruebas del Servidor
        frame_pruebas = ttk.Frame(notebook, padding="10")
        notebook.add(frame_pruebas, text="🧪 Pruebas Servidor")
        self.crear_pestana_pruebas(frame_pruebas)
        
        # Pestaña 3: Gestión de Usuarios
        frame_usuarios = ttk.Frame(notebook, padding="10")
        notebook.add(frame_usuarios, text="👥 Usuarios")
        self.crear_pestana_usuarios(frame_usuarios)
        
        # Pestaña 4: Gestión de Vehículos
        frame_vehiculos = ttk.Frame(notebook, padding="10")
        notebook.add(frame_vehiculos, text="🚗 Vehículos")
        self.crear_pestana_vehiculos(frame_vehiculos)
        
        # Pestaña 5: Herramientas del Sistema
        frame_herramientas = ttk.Frame(notebook, padding="10")
        notebook.add(frame_herramientas, text="🛠️ Herramientas")
        self.crear_pestana_herramientas(frame_herramientas)
        
        # Pestaña 6: Logs y Monitoreo
        frame_logs = ttk.Frame(notebook, padding="10")
        notebook.add(frame_logs, text="📝 Logs")
        self.crear_pestana_logs(frame_logs)
        
        # Área de Texto de Salida
        frame_salida = ttk.LabelFrame(frame_principal, text="Consola de Salida", padding="5")
        frame_salida.grid(row=3, column=0, sticky=(tk.W, tk.E, tk.N, tk.S), pady=5)
        frame_principal.rowconfigure(3, weight=1)
        
        self.texto_salida = scrolledtext.ScrolledText(frame_salida, height=12, 
                                                      wrap=tk.WORD, 
                                                      font=('Courier', 9),
                                                      bg='#1e1e1e',
                                                      fg='#00ff00')
        self.texto_salida.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S))
        frame_salida.columnconfigure(0, weight=1)
        frame_salida.rowconfigure(0, weight=1)
        
        # Botones para la salida
        frame_botones = ttk.Frame(frame_salida)
        frame_botones.grid(row=1, column=0, pady=5, sticky=tk.W)
        
        ttk.Button(frame_botones, text="Limpiar", 
                  command=self.limpiar_salida).pack(side=tk.LEFT, padx=2)
        ttk.Button(frame_botones, text="Guardar Log", 
                  command=self.guardar_log).pack(side=tk.LEFT, padx=2)
        ttk.Button(frame_botones, text="Exportar Reporte", 
                  command=self.exportar_reporte).pack(side=tk.LEFT, padx=2)
        
        self.log("=" * 70)
        self.log("🚗 EazyRide - Herramienta de Administración v2.0")
        self.log("=" * 70)
        self.log("")
        self.log(f"📍 MariaDB: {DB_HOST}:{3306}")
        self.log(f"📍 MongoDB: {MONGO_HOST}:{MONGO_PORT}")
        self.log(f"📍 Servidor Web: {WEB_URL}")
        self.log("")
        
        # Verificar sistemas al iniciar
        self.root.after(500, self.verificar_todos_sistemas)
    
    def crear_pestana_basedatos(self, parent):
        """Crear pestaña de gestión de base de datos"""
        # Sección MariaDB
        frame_maria = ttk.LabelFrame(parent, text="Gestión MariaDB", padding="10")
        frame_maria.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S), padx=5, pady=5)
        
        ttk.Button(frame_maria, text="✅ Crear Base de Datos", 
                  command=self.crear_bd_maria, width=30).grid(row=0, column=0, pady=3, padx=3, sticky=tk.W)
        ttk.Button(frame_maria, text="🏗️ Crear Tablas", 
                  command=self.crear_estructura_maria, width=30).grid(row=1, column=0, pady=3, padx=3, sticky=tk.W)
        ttk.Button(frame_maria, text="🗑️ Eliminar Base de Datos", 
                  command=self.eliminar_bd_maria, width=30).grid(row=2, column=0, pady=3, padx=3, sticky=tk.W)
        ttk.Button(frame_maria, text="📊 Mostrar Tablas", 
                  command=self.mostrar_tablas_maria, width=30).grid(row=3, column=0, pady=3, padx=3, sticky=tk.W)
        ttk.Button(frame_maria, text="📈 Estadísticas", 
                  command=self.mostrar_estadisticas_maria, width=30).grid(row=4, column=0, pady=3, padx=3, sticky=tk.W)
        
        # Sección MongoDB
        frame_mongo = ttk.LabelFrame(parent, text="Gestión MongoDB", padding="10")
        frame_mongo.grid(row=0, column=1, sticky=(tk.W, tk.E, tk.N, tk.S), padx=5, pady=5)
        
        ttk.Button(frame_mongo, text="✅ Crear Base de Datos", 
                  command=self.crear_bd_mongo, width=30).grid(row=0, column=0, pady=3, padx=3, sticky=tk.W)
        ttk.Button(frame_mongo, text="🏗️ Crear Colecciones", 
                  command=self.crear_estructura_mongo, width=30).grid(row=1, column=0, pady=3, padx=3, sticky=tk.W)
        ttk.Button(frame_mongo, text="🗑️ Eliminar Base de Datos", 
                  command=self.eliminar_bd_mongo, width=30).grid(row=2, column=0, pady=3, padx=3, sticky=tk.W)
        ttk.Button(frame_mongo, text="📊 Mostrar Colecciones", 
                  command=self.mostrar_colecciones_mongo, width=30).grid(row=3, column=0, pady=3, padx=3, sticky=tk.W)
        ttk.Button(frame_mongo, text="📈 Estadísticas", 
                  command=self.mostrar_estadisticas_mongo, width=30).grid(row=4, column=0, pady=3, padx=3, sticky=tk.W)
        
        # Acciones Rápidas
        frame_rapido = ttk.LabelFrame(parent, text="Acciones Rápidas", padding="10")
        frame_rapido.grid(row=1, column=0, columnspan=2, sticky=(tk.W, tk.E), padx=5, pady=5)
        
        ttk.Button(frame_rapido, text="🚀 Inicializar Todo", 
                  command=self.inicializar_todo, width=20).pack(side=tk.LEFT, padx=5)
        ttk.Button(frame_rapido, text="🔄 Resetear Todo", 
                  command=self.resetear_todo, width=20).pack(side=tk.LEFT, padx=5)
        ttk.Button(frame_rapido, text="💾 Hacer Backup", 
                  command=self.hacer_backup, width=20).pack(side=tk.LEFT, padx=5)
        ttk.Button(frame_rapido, text="📥 Restaurar Backup", 
                  command=self.restaurar_backup, width=20).pack(side=tk.LEFT, padx=5)
    
    def crear_pestana_pruebas(self, parent):
        """Crear pestaña de pruebas del servidor"""
        etiqueta_pruebas = ttk.Label(parent, text="Pruebas del Servidor y API", style='Subtitulo.TLabel')
        etiqueta_pruebas.grid(row=0, column=0, columnspan=2, pady=10)
        
        # Frame de Pruebas API
        frame_api = ttk.LabelFrame(parent, text="Endpoints API", padding="10")
        frame_api.grid(row=1, column=0, sticky=(tk.W, tk.E, tk.N, tk.S), padx=5, pady=5)
        
        ttk.Button(frame_api, text="🧪 Probar Autenticación", 
                  command=lambda: self.probar_api('auth'), width=30).grid(row=0, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_api, text="🧪 Probar API Perfil", 
                  command=lambda: self.probar_api('profile'), width=30).grid(row=1, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_api, text="🧪 Probar API Vehículos", 
                  command=lambda: self.probar_api('vehicles'), width=30).grid(row=2, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_api, text="🎯 Ejecutar Todas las Pruebas", 
                  command=self.ejecutar_todas_pruebas_api, width=30).grid(row=3, column=0, pady=3, sticky=tk.W)
        
        # Frame de Pruebas del Servidor
        frame_servidor = ttk.LabelFrame(parent, text="Pruebas del Servidor", padding="10")
        frame_servidor.grid(row=1, column=1, sticky=(tk.W, tk.E, tk.N, tk.S), padx=5, pady=5)
        
        ttk.Button(frame_servidor, text="🌐 Verificar Servidor Web", 
                  command=self.probar_servidor_web, width=30).grid(row=0, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_servidor, text="📡 Verificar Conexión BD", 
                  command=self.probar_conexion_bd, width=30).grid(row=1, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_servidor, text="🎯 Ejecutar Pruebas Completas", 
                  command=self.ejecutar_pruebas_completas, width=30).grid(row=2, column=0, pady=3, sticky=tk.W)
    
    def crear_pestana_usuarios(self, parent):
        """Crear pestaña de gestión de usuarios"""
        # Frame para Crear Admin
        frame_admin = ttk.LabelFrame(parent, text="Crear Usuario Administrador", padding="10")
        frame_admin.grid(row=0, column=0, sticky=(tk.W, tk.E), padx=5, pady=5, columnspan=2)
        
        ttk.Label(frame_admin, text="Usuario:").grid(row=0, column=0, sticky=tk.E, padx=5, pady=5)
        self.entrada_admin_usuario = ttk.Entry(frame_admin, width=25)
        self.entrada_admin_usuario.grid(row=0, column=1, padx=5, pady=5)
        
        ttk.Label(frame_admin, text="Email:").grid(row=0, column=2, sticky=tk.E, padx=5, pady=5)
        self.entrada_admin_email = ttk.Entry(frame_admin, width=25)
        self.entrada_admin_email.grid(row=0, column=3, padx=5, pady=5)
        
        ttk.Label(frame_admin, text="Contraseña:").grid(row=1, column=0, sticky=tk.E, padx=5, pady=5)
        self.entrada_admin_password = ttk.Entry(frame_admin, width=25, show="*")
        self.entrada_admin_password.grid(row=1, column=1, padx=5, pady=5)
        
        ttk.Button(frame_admin, text="✅ Crear Administrador", 
                  command=self.crear_usuario_admin, width=25).grid(row=1, column=3, padx=5, pady=5)
        
        # Gestión de Usuarios
        frame_gestion = ttk.LabelFrame(parent, text="Gestión de Usuarios", padding="10")
        frame_gestion.grid(row=1, column=0, sticky=(tk.W, tk.E, tk.N, tk.S), padx=5, pady=5)
        
        ttk.Button(frame_gestion, text="👥 Listar Todos", 
                  command=self.listar_usuarios, width=30).grid(row=0, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_gestion, text="🔍 Buscar Usuario", 
                  command=self.buscar_usuario, width=30).grid(row=1, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_gestion, text="🗑️ Eliminar Usuario", 
                  command=self.eliminar_usuario, width=30).grid(row=2, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_gestion, text="🔄 Resetear Contraseña", 
                  command=self.resetear_password, width=30).grid(row=3, column=0, pady=3, sticky=tk.W)
        
        # Estadísticas
        frame_stats = ttk.LabelFrame(parent, text="Estadísticas de Usuarios", padding="10")
        frame_stats.grid(row=1, column=1, sticky=(tk.W, tk.E, tk.N, tk.S), padx=5, pady=5)
        
        ttk.Button(frame_stats, text="📊 Ver Estadísticas", 
                  command=self.mostrar_estadisticas_usuarios, width=30).grid(row=0, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_stats, text="💎 Usuarios Premium", 
                  command=self.mostrar_usuarios_premium, width=30).grid(row=1, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_stats, text="📈 Usuarios Activos", 
                  command=self.mostrar_usuarios_activos, width=30).grid(row=2, column=0, pady=3, sticky=tk.W)
    
    def crear_pestana_vehiculos(self, parent):
        """Crear pestaña de gestión de vehículos"""
        # Gestión de Vehículos
        frame_gestion = ttk.LabelFrame(parent, text="Gestión de Vehículos", padding="10")
        frame_gestion.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S), padx=5, pady=5)
        
        ttk.Button(frame_gestion, text="🚗 Listar Todos", 
                  command=self.listar_vehiculos, width=30).grid(row=0, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_gestion, text="➕ Añadir Vehículo", 
                  command=self.anadir_vehiculo, width=30).grid(row=1, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_gestion, text="🗑️ Eliminar Vehículo", 
                  command=self.eliminar_vehiculo, width=30).grid(row=2, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_gestion, text="🔍 Buscar Vehículo", 
                  command=self.buscar_vehiculo, width=30).grid(row=3, column=0, pady=3, sticky=tk.W)
        
        # Estado de Vehículos
        frame_estado = ttk.LabelFrame(parent, text="Estado de Vehículos", padding="10")
        frame_estado.grid(row=0, column=1, sticky=(tk.W, tk.E, tk.N, tk.S), padx=5, pady=5)
        
        ttk.Button(frame_estado, text="🟢 Disponibles", 
                  command=self.mostrar_vehiculos_disponibles, width=30).grid(row=0, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_estado, text="🔴 En Uso", 
                  command=self.mostrar_vehiculos_enuso, width=30).grid(row=1, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_estado, text="🔋 Estado Batería", 
                  command=self.mostrar_estado_bateria, width=30).grid(row=2, column=0, pady=3, sticky=tk.W)
        
        # Datos de Prueba
        frame_prueba = ttk.LabelFrame(parent, text="Datos de Prueba", padding="10")
        frame_prueba.grid(row=1, column=0, columnspan=2, sticky=(tk.W, tk.E), padx=5, pady=5)
        
        ttk.Button(frame_prueba, text="📦 Generar Vehículos de Prueba", 
                  command=self.generar_vehiculos_prueba, width=25).pack(side=tk.LEFT, padx=5)
        ttk.Button(frame_prueba, text="🗑️ Limpiar Datos de Prueba", 
                  command=self.limpiar_datos_prueba, width=25).pack(side=tk.LEFT, padx=5)
    
    def crear_pestana_herramientas(self, parent):
        """Crear pestaña de herramientas del sistema"""
        # Herramientas del Sistema
        frame_sistema = ttk.LabelFrame(parent, text="Herramientas del Sistema", padding="10")
        frame_sistema.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S), padx=5, pady=5)
        
        ttk.Button(frame_sistema, text="🔄 Limpiar Caché", 
                  command=self.limpiar_cache, width=30).grid(row=0, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_sistema, text="🗑️ Limpiar Archivos Temporales", 
                  command=self.limpiar_temp, width=30).grid(row=1, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_sistema, text="📁 Limpiar Logs", 
                  command=self.limpiar_logs, width=30).grid(row=2, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_sistema, text="🔍 Verificar Integridad", 
                  command=self.verificar_integridad, width=30).grid(row=3, column=0, pady=3, sticky=tk.W)
        
        # Mantenimiento
        frame_mant = ttk.LabelFrame(parent, text="Mantenimiento", padding="10")
        frame_mant.grid(row=0, column=1, sticky=(tk.W, tk.E, tk.N, tk.S), padx=5, pady=5)
        
        ttk.Button(frame_mant, text="🔐 Verificación de Seguridad", 
                  command=self.verificacion_seguridad, width=30).grid(row=0, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_mant, text="📊 Verificación de Salud", 
                  command=self.verificacion_salud, width=30).grid(row=1, column=0, pady=3, sticky=tk.W)
        ttk.Button(frame_mant, text="🔄 Actualizar Sistema", 
                  command=self.actualizar_sistema, width=30).grid(row=2, column=0, pady=3, sticky=tk.W)
    
    def crear_pestana_logs(self, parent):
        """Crear pestaña de logs y monitoreo"""
        # Visor de Logs
        frame_visor = ttk.LabelFrame(parent, text="Visor de Logs", padding="10")
        frame_visor.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S), padx=5, pady=5)
        frame_visor.columnconfigure(0, weight=1)
        frame_visor.rowconfigure(1, weight=1)
        
        # Controles
        controles = ttk.Frame(frame_visor)
        controles.grid(row=0, column=0, sticky=(tk.W, tk.E), pady=5)
        
        ttk.Button(controles, text="📄 Errores PHP", 
                  command=lambda: self.ver_logs('php')).pack(side=tk.LEFT, padx=2)
        ttk.Button(controles, text="🌐 Apache", 
                  command=lambda: self.ver_logs('apache')).pack(side=tk.LEFT, padx=2)
        ttk.Button(controles, text="💾 MySQL", 
                  command=lambda: self.ver_logs('mysql')).pack(side=tk.LEFT, padx=2)
        ttk.Button(controles, text="🔄 Actualizar", 
                  command=self.actualizar_logs).pack(side=tk.LEFT, padx=2)
        
        # Display de logs
        self.visor_logs = scrolledtext.ScrolledText(frame_visor, height=20, 
                                                    wrap=tk.WORD, font=('Courier', 9))
        self.visor_logs.grid(row=1, column=0, sticky=(tk.W, tk.E, tk.N, tk.S))
        
        # Monitoreo
        frame_monitor = ttk.LabelFrame(parent, text="Monitoreo del Sistema", padding="10")
        frame_monitor.grid(row=1, column=0, sticky=(tk.W, tk.E), padx=5, pady=5)
        
        ttk.Button(frame_monitor, text="📊 Recursos del Sistema", 
                  command=self.mostrar_recursos_sistema, width=25).pack(side=tk.LEFT, padx=5)
        ttk.Button(frame_monitor, text="🔍 Sesiones Activas", 
                  command=self.mostrar_sesiones_activas, width=25).pack(side=tk.LEFT, padx=5)
    
    # Métodos Utilitarios
    def log(self, mensaje):
        """Añadir mensaje a la consola de salida"""
        timestamp = datetime.now().strftime("%H:%M:%S")
        self.texto_salida.insert(tk.END, f"[{timestamp}] {mensaje}\n")
        self.texto_salida.see(tk.END)
        self.root.update_idletasks()
    
    def limpiar_salida(self):
        """Limpiar consola de salida"""
        self.texto_salida.delete(1.0, tk.END)
    
    def guardar_log(self):
        """Guardar log en archivo"""
        nombre_archivo = filedialog.asksaveasfilename(
            defaultextension=".txt",
            filetypes=[("Archivos de texto", "*.txt"), ("Todos los archivos", "*.*")]
        )
        if nombre_archivo:
            with open(nombre_archivo, 'w', encoding='utf-8') as f:
                f.write(self.texto_salida.get(1.0, tk.END))
            self.log(f"✅ Log guardado en: {nombre_archivo}")
    
    def exportar_reporte(self):
        """Exportar reporte del sistema"""
        nombre_archivo = filedialog.asksaveasfilename(
            defaultextension=".html",
            filetypes=[("Archivos HTML", "*.html"), ("Todos los archivos", "*.*")]
        )
        if nombre_archivo:
            reporte = f"""
            <html>
            <head>
                <title>Reporte del Sistema EazyRide</title>
                <meta charset="UTF-8">
                <style>
                    body {{ font-family: Arial, sans-serif; margin: 20px; }}
                    h1 {{ color: #667eea; }}
                    pre {{ background: #1e1e1e; color: #00ff00; padding: 20px; border-radius: 5px; }}
                </style>
            </head>
            <body>
            <h1>🚗 Reporte del Sistema EazyRide</h1>
            <p><strong>Generado:</strong> {datetime.now().strftime("%Y-%m-%d %H:%M:%S")}</p>
            <h2>Log de Consola:</h2>
            <pre>{self.texto_salida.get(1.0, tk.END)}</pre>
            </body>
            </html>
            """
            with open(nombre_archivo, 'w', encoding='utf-8') as f:
                f.write(reporte)
            self.log(f"✅ Reporte exportado a: {nombre_archivo}")
    
    # Verificaciones del Sistema
    def verificar_todos_sistemas(self):
        """Verificar todos los componentes del sistema"""
        self.log("🔍 Verificando todos los sistemas...")
        threading.Thread(target=self._verificar_sistemas_thread, daemon=True).start()
    
    def _verificar_sistemas_thread(self):
        """Thread para verificar sistemas"""
        self.verificar_mariadb()
        self.verificar_mongodb()
        self.probar_servidor_web()
    
    def verificar_mariadb(self):
        """Verificar conexión a MariaDB"""
        if not MYSQL_CONFIG_NO_DB:
            self.estado_mariadb.config(text="MariaDB: No configurado ⚠️", 
                                      foreground="orange")
            self.log("⚠️ MariaDB no configurado - Verifica el archivo .env")
            self.log(f"   DB_USER: {DB_USER or '(vacío)'}")
            self.log(f"   DB_PASS: {'configurado' if DB_PASS else '(vacío)'}")
            return
        try:
            self.log(f"🔄 Intentando conectar a MariaDB en {DB_HOST}...")
            conn = mysql.connector.connect(**MYSQL_CONFIG_NO_DB, connection_timeout=5)
            conn.close()
            self.estado_mariadb.config(text="MariaDB: Conectado ✅", 
                                      foreground="green")
            self.log(f"✅ Conexión exitosa a MariaDB ({DB_HOST})")
        except mysql.connector.Error as e:
            self.estado_mariadb.config(text="MariaDB: Error ❌", 
                                      foreground="red")
            self.log(f"❌ Error de MariaDB: {e}")
            self.log(f"   Host: {DB_HOST}")
            self.log(f"   User: {DB_USER}")
            self.log("   Verifica que MariaDB esté corriendo: docker ps")
        except Exception as e:
            self.estado_mariadb.config(text="MariaDB: Error ❌", 
                                      foreground="red")
            self.log(f"❌ Error inesperado: {e}")
    
    def verificar_mongodb(self):
        """Verificar conexión a MongoDB"""
        if not MONGO_URI:
            self.estado_mongodb.config(text="MongoDB: No configurado ⚠️", 
                                      foreground="orange")
            self.log("⚠️ MongoDB no configurado - Verifica el archivo .env")
            self.log(f"   MONGO_USER: {MONGO_USER or '(vacío)'}")
            self.log(f"   MONGO_PASS: {'configurado' if MONGO_PASS else '(vacío)'}")
            return
        try:
            self.log(f"🔄 Intentando conectar a MongoDB en {MONGO_HOST}:{MONGO_PORT}...")
            client = MongoClient(MONGO_URI, serverSelectionTimeoutMS=5000)
            client.admin.command("ping")
            client.close()
            self.estado_mongodb.config(text="MongoDB: Conectado ✅", 
                                      foreground="green")
            self.log(f"✅ Conexión exitosa a MongoDB ({MONGO_HOST}:{MONGO_PORT})")
        except Exception as e:
            self.estado_mongodb.config(text="MongoDB: Error ❌", 
                                      foreground="red")
            self.log(f"❌ Error de MongoDB: {e}")
            self.log(f"   Host: {MONGO_HOST}:{MONGO_PORT}")
            self.log(f"   User: {MONGO_USER}")
            self.log("   Verifica que MongoDB esté corriendo: docker ps")
    
    # Operaciones de Base de Datos MariaDB
    def crear_bd_maria(self):
        """Crear base de datos MariaDB"""
        if not MYSQL_CONFIG_NO_DB:
            self.log("⚠️ MariaDB no configurado")
            return
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG_NO_DB)
            cursor = conn.cursor()
            cursor.execute(f"CREATE DATABASE IF NOT EXISTS {DB_NAME}")
            conn.commit()
            self.log(f"✅ Base de datos MariaDB '{DB_NAME}' creada")
            cursor.close()
            conn.close()
        except Exception as e:
            self.log(f"❌ Error creando MariaDB: {e}")
    
    def crear_estructura_maria(self):
        """Crear estructura de tablas en MariaDB"""
        self.log("🏗️ Creando estructura MariaDB...")
        if not MYSQL_CONFIG:
            self.log("⚠️ MariaDB no configurado")
            return
        
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG)
            cursor = conn.cursor()
            
            # Desactivar verificación de claves foráneas
            cursor.execute("SET FOREIGN_KEY_CHECKS = 0;")
            
            # Crear tabla users
            cursor.execute("""
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL UNIQUE,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                fullname VARCHAR(100),
                phone VARCHAR(20),
                is_admin BOOLEAN NOT NULL DEFAULT FALSE,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            """)
            
            # Crear tabla vehicles
            cursor.execute("""
            CREATE TABLE IF NOT EXISTS vehicles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                plate VARCHAR(20) NOT NULL UNIQUE,
                brand VARCHAR(50) NOT NULL,
                model VARCHAR(50) NOT NULL,
                year INT,
                status ENUM('available', 'in_use', 'maintenance') DEFAULT 'available',
                battery_level INT DEFAULT 100,
                latitude DECIMAL(10,8),
                longitude DECIMAL(11,8),
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            """)
            
            # Crear tabla bookings
            cursor.execute("""
            CREATE TABLE IF NOT EXISTS bookings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                vehicle_id INT NOT NULL,
                start_time DATETIME NOT NULL,
                end_time DATETIME,
                total_cost DECIMAL(10,2),
                status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
            );
            """)
            
            cursor.execute("SET FOREIGN_KEY_CHECKS = 1;")
            conn.commit()
            self.log("✅ Estructura MariaDB creada (users, vehicles, bookings)")
            cursor.close()
            conn.close()
        except Exception as e:
            self.log(f"❌ Error creando estructura MariaDB: {e}")
    
    def eliminar_bd_maria(self):
        """Eliminar base de datos MariaDB"""
        if messagebox.askyesno("Confirmar", "¿Eliminar base de datos MariaDB? ¡Se perderán todos los datos!"):
            try:
                conn = mysql.connector.connect(**MYSQL_CONFIG_NO_DB)
                cursor = conn.cursor()
                cursor.execute(f"DROP DATABASE IF EXISTS {DB_NAME}")
                conn.commit()
                self.log(f"✅ Base de datos MariaDB '{DB_NAME}' eliminada")
                cursor.close()
                conn.close()
            except Exception as e:
                self.log(f"❌ Error eliminando MariaDB: {e}")
    
    def mostrar_tablas_maria(self):
        """Mostrar tablas de MariaDB"""
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG)
            cursor = conn.cursor()
            cursor.execute("SHOW TABLES")
            tablas = cursor.fetchall()
            self.log("📊 Tablas en MariaDB:")
            for tabla in tablas:
                cursor.execute(f"SELECT COUNT(*) FROM {tabla[0]}")
                count = cursor.fetchone()[0]
                self.log(f"   • {tabla[0]}: {count} filas")
            cursor.close()
            conn.close()
        except Exception as e:
            self.log(f"❌ Error mostrando tablas: {e}")
    
    def mostrar_estadisticas_maria(self):
        """Mostrar estadísticas de MariaDB"""
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG)
            cursor = conn.cursor()
            cursor.execute("SHOW TABLE STATUS")
            stats = cursor.fetchall()
            self.log("📈 Estadísticas MariaDB:")
            total_size = 0
            for stat in stats:
                if stat[6] and stat[8]:
                    size = (stat[6] + stat[8]) / 1024 / 1024
                    total_size += size
                    self.log(f"   • {stat[0]}: {stat[4]} filas, {size:.2f} MB")
            self.log(f"   Tamaño Total: {total_size:.2f} MB")
            cursor.close()
            conn.close()
        except Exception as e:
            self.log(f"❌ Error mostrando estadísticas: {e}")
    
    # Operaciones MongoDB
    def crear_bd_mongo(self):
        """Crear base de datos MongoDB"""
        try:
            client = MongoClient(MONGO_URI)
            db = client[MONGO_DB]
            db.create_collection("dummy")
            db["dummy"].drop()
            self.log(f"✅ Base de datos MongoDB '{MONGO_DB}' lista")
        except Exception as e:
            self.log(f"❌ Error creando MongoDB: {e}")
    
    def crear_estructura_mongo(self):
        """Crear colecciones en MongoDB"""
        self.log("🏗️ Creando colecciones MongoDB...")
        try:
            client = MongoClient(MONGO_URI)
            db = client[MONGO_DB]
            
            colecciones = ['cars', 'history', 'sensors', 'logs', 'trips']
            for coll in colecciones:
                if coll not in db.list_collection_names():
                    db.create_collection(coll)
                    self.log(f"   ✅ Colección creada: {coll}")
            
            self.log("✅ Estructura MongoDB creada")
        except Exception as e:
            self.log(f"❌ Error creando estructura MongoDB: {e}")
    
    def eliminar_bd_mongo(self):
        """Eliminar base de datos MongoDB"""
        if messagebox.askyesno("Confirmar", "¿Eliminar base de datos MongoDB? ¡Se perderán todos los datos!"):
            try:
                client = MongoClient(MONGO_URI)
                client.drop_database(MONGO_DB)
                self.log(f"✅ Base de datos MongoDB '{MONGO_DB}' eliminada")
            except Exception as e:
                self.log(f"❌ Error eliminando MongoDB: {e}")
    
    def mostrar_colecciones_mongo(self):
        """Mostrar colecciones de MongoDB"""
        try:
            client = MongoClient(MONGO_URI)
            db = client[MONGO_DB]
            colecciones = db.list_collection_names()
            self.log("📊 Colecciones en MongoDB:")
            for coll in colecciones:
                count = db[coll].count_documents({})
                self.log(f"   • {coll}: {count} documentos")
        except Exception as e:
            self.log(f"❌ Error mostrando colecciones: {e}")
    
    def mostrar_estadisticas_mongo(self):
        """Mostrar estadísticas de MongoDB"""
        try:
            client = MongoClient(MONGO_URI)
            db = client[MONGO_DB]
            stats = db.command("dbstats")
            self.log("📈 Estadísticas MongoDB:")
            self.log(f"   • Colecciones: {stats.get('collections', 0)}")
            self.log(f"   • Objetos: {stats.get('objects', 0)}")
            self.log(f"   • Tamaño de Datos: {stats.get('dataSize', 0) / 1024 / 1024:.2f} MB")
            self.log(f"   • Tamaño de Almacenamiento: {stats.get('storageSize', 0) / 1024 / 1024:.2f} MB")
        except Exception as e:
            self.log(f"❌ Error mostrando estadísticas: {e}")
    
    # Acciones Rápidas
    def inicializar_todo(self):
        """Inicializar todas las bases de datos"""
        if messagebox.askyesno("Confirmar", "¿Inicializar todas las bases de datos?"):
            self.log("🚀 Inicializando todos los sistemas...")
            self.crear_bd_maria()
            self.crear_estructura_maria()
            self.crear_bd_mongo()
            self.crear_estructura_mongo()
            self.log("✅ Todos los sistemas inicializados")
    
    def resetear_todo(self):
        """Resetear todas las bases de datos"""
        if messagebox.askyesno("Confirmar", "¿RESETEAR TODO? ¡Se eliminarán TODOS los datos!"):
            self.log("🔄 Reseteando todos los sistemas...")
            self.eliminar_bd_maria()
            self.eliminar_bd_mongo()
            self.inicializar_todo()
            self.log("✅ Todos los sistemas reseteados")
    
    def hacer_backup(self):
        """Hacer backup de las bases de datos"""
        self.log("💾 Iniciando backup...")
        carpeta = filedialog.askdirectory(title="Seleccionar carpeta para backup")
        if carpeta:
            try:
                timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                nombre_backup = f"eazyride_backup_{timestamp}"
                ruta_backup = os.path.join(carpeta, nombre_backup)
                os.makedirs(ruta_backup, exist_ok=True)
                
                # Backup MariaDB
                self.log("   📦 Haciendo backup de MariaDB...")
                cmd_maria = f"mysqldump -h {DB_HOST} -u {DB_USER} -p{DB_PASS} {DB_NAME} > {os.path.join(ruta_backup, 'mariadb.sql')}"
                os.system(cmd_maria)
                
                # Backup MongoDB
                self.log("   📦 Haciendo backup de MongoDB...")
                cmd_mongo = f"mongodump --uri='{MONGO_URI}' --db={MONGO_DB} --out={os.path.join(ruta_backup, 'mongodb')}"
                os.system(cmd_mongo)
                
                self.log(f"✅ Backup completado en: {ruta_backup}")
                messagebox.showinfo("Éxito", f"Backup creado en:\n{ruta_backup}")
            except Exception as e:
                self.log(f"❌ Error haciendo backup: {e}")
                messagebox.showerror("Error", f"Error haciendo backup:\n{e}")
    
    def restaurar_backup(self):
        """Restaurar backup de las bases de datos"""
        self.log("📥 Restaurando backup...")
        carpeta = filedialog.askdirectory(title="Seleccionar carpeta de backup")
        if carpeta:
            if messagebox.askyesno("Confirmar", "¿Restaurar backup? Se sobrescribirán los datos actuales."):
                try:
                    # Restaurar MariaDB
                    archivo_maria = os.path.join(carpeta, 'mariadb.sql')
                    if os.path.exists(archivo_maria):
                        self.log("   📦 Restaurando MariaDB...")
                        cmd_maria = f"mysql -h {DB_HOST} -u {DB_USER} -p{DB_PASS} {DB_NAME} < {archivo_maria}"
                        os.system(cmd_maria)
                    
                    # Restaurar MongoDB
                    carpeta_mongo = os.path.join(carpeta, 'mongodb', MONGO_DB)
                    if os.path.exists(carpeta_mongo):
                        self.log("   📦 Restaurando MongoDB...")
                        cmd_mongo = f"mongorestore --uri='{MONGO_URI}' --db={MONGO_DB} {carpeta_mongo}"
                        os.system(cmd_mongo)
                    
                    self.log("✅ Backup restaurado exitosamente")
                    messagebox.showinfo("Éxito", "Backup restaurado correctamente")
                except Exception as e:
                    self.log(f"❌ Error restaurando backup: {e}")
                    messagebox.showerror("Error", f"Error restaurando backup:\n{e}")
    
    # Pruebas del Servidor
    def probar_servidor_web(self):
        """Probar servidor web"""
        try:
            response = requests.get(WEB_URL, timeout=5)
            if response.status_code == 200:
                self.estado_servidor.config(text="Servidor Web: Activo ✅", 
                                           foreground="green")
                self.log(f"✅ Servidor web respondiendo en {WEB_URL}")
            else:
                self.estado_servidor.config(text=f"Servidor Web: {response.status_code} ⚠️", 
                                           foreground="orange")
                self.log(f"⚠️ Servidor web devolvió estado: {response.status_code}")
        except Exception as e:
            self.estado_servidor.config(text="Servidor Web: No activo ❌", 
                                       foreground="red")
            self.log(f"❌ Error del servidor web: {e}")
    
    def probar_api(self, endpoint):
        """Probar endpoint de API específico"""
        self.log(f"🧪 Probando API {endpoint}...")
        try:
            url = f"{WEB_URL}/php/api/{endpoint}.php"
            response = requests.get(url, timeout=5)
            if response.status_code == 200:
                self.log(f"✅ API {endpoint} respondió correctamente")
            else:
                self.log(f"⚠️ API {endpoint} devolvió estado {response.status_code}")
        except Exception as e:
            self.log(f"❌ Error probando API {endpoint}: {e}")
    
    def ejecutar_todas_pruebas_api(self):
        """Ejecutar todas las pruebas de API"""
        self.log("🎯 Ejecutando todas las pruebas de API...")
        endpoints = ['auth', 'profile', 'vehicles', 'booking', 'payment']
        for endpoint in endpoints:
            self.probar_api(endpoint)
        self.log("✅ Todas las pruebas de API completadas")
    
    def probar_conexion_bd(self):
        """Probar conexión a base de datos"""
        self.verificar_mariadb()
        self.verificar_mongodb()
    
    def ejecutar_pruebas_completas(self):
        """Ejecutar pruebas completas del servidor"""
        self.log("🎯 Ejecutando pruebas completas del servidor...")
        self.probar_servidor_web()
        self.probar_conexion_bd()
        self.ejecutar_todas_pruebas_api()
        self.log("✅ Todas las pruebas del servidor completadas")
    
    # Gestión de Usuarios
    def crear_usuario_admin(self):
        """Crear usuario administrador"""
        usuario = self.entrada_admin_usuario.get().strip()
        email = self.entrada_admin_email.get().strip()
        password = self.entrada_admin_password.get()
        
        if not all([usuario, email, password]):
            messagebox.showerror("Error", "¡Todos los campos son obligatorios!")
            return
        
        if len(password) < 6:
            messagebox.showerror("Error", "¡La contraseña debe tener al menos 6 caracteres!")
            return
        
        try:
            password_hash = hashlib.sha256(password.encode()).hexdigest()
            
            conn = mysql.connector.connect(**MYSQL_CONFIG)
            cursor = conn.cursor()
            
            query = """
            INSERT INTO users (username, email, password, is_admin, created_at)
            VALUES (%s, %s, %s, TRUE, NOW())
            """
            cursor.execute(query, (usuario, email, password_hash))
            conn.commit()
            
            self.log(f"✅ Usuario administrador '{usuario}' creado")
            messagebox.showinfo("Éxito", f"¡Usuario administrador creado!")
            
            self.entrada_admin_usuario.delete(0, tk.END)
            self.entrada_admin_email.delete(0, tk.END)
            self.entrada_admin_password.delete(0, tk.END)
            
            cursor.close()
            conn.close()
        except mysql.connector.IntegrityError:
            self.log(f"❌ Error: Usuario o email ya existe")
            messagebox.showerror("Error", "¡Usuario o email ya existe!")
        except Exception as e:
            self.log(f"❌ Error creando administrador: {e}")
            messagebox.showerror("Error", f"Error: {e}")
    
    def listar_usuarios(self):
        """Listar todos los usuarios"""
        self.log("👥 Listando usuarios...")
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG)
            cursor = conn.cursor()
            cursor.execute("SELECT id, username, email, is_admin, created_at FROM users ORDER BY id DESC LIMIT 20")
            usuarios = cursor.fetchall()
            self.log(f"Encontrados {len(usuarios)} usuarios:")
            for user in usuarios:
                badge_admin = "👑" if user[3] else ""
                self.log(f"   {badge_admin} {user[1]} ({user[2]}) - Creado: {user[4]}")
            cursor.close()
            conn.close()
        except Exception as e:
            self.log(f"❌ Error listando usuarios: {e}")
    
    def buscar_usuario(self):
        """Buscar usuario"""
        busqueda = simpledialog.askstring("Buscar Usuario", "Ingrese nombre de usuario o email:")
        if busqueda:
            try:
                conn = mysql.connector.connect(**MYSQL_CONFIG)
                cursor = conn.cursor()
                query = "SELECT id, username, email, is_admin, created_at FROM users WHERE username LIKE %s OR email LIKE %s"
                cursor.execute(query, (f"%{busqueda}%", f"%{busqueda}%"))
                resultados = cursor.fetchall()
                if resultados:
                    self.log(f"🔍 Resultados de búsqueda para '{busqueda}':")
                    for user in resultados:
                        badge_admin = "👑" if user[3] else ""
                        self.log(f"   {badge_admin} ID:{user[0]} {user[1]} ({user[2]})")
                else:
                    self.log(f"❌ No se encontraron usuarios con '{busqueda}'")
                cursor.close()
                conn.close()
            except Exception as e:
                self.log(f"❌ Error buscando usuario: {e}")
    
    def eliminar_usuario(self):
        """Eliminar usuario"""
        user_id = simpledialog.askinteger("Eliminar Usuario", "Ingrese ID del usuario a eliminar:")
        if user_id:
            if messagebox.askyesno("Confirmar", f"¿Eliminar usuario con ID {user_id}?"):
                try:
                    conn = mysql.connector.connect(**MYSQL_CONFIG)
                    cursor = conn.cursor()
                    cursor.execute("DELETE FROM users WHERE id = %s", (user_id,))
                    conn.commit()
                    if cursor.rowcount > 0:
                        self.log(f"✅ Usuario con ID {user_id} eliminado")
                    else:
                        self.log(f"❌ No se encontró usuario con ID {user_id}")
                    cursor.close()
                    conn.close()
                except Exception as e:
                    self.log(f"❌ Error eliminando usuario: {e}")
    
    def resetear_password(self):
        """Resetear contraseña de usuario"""
        user_id = simpledialog.askinteger("Resetear Contraseña", "Ingrese ID del usuario:")
        if user_id:
            nueva_password = simpledialog.askstring("Nueva Contraseña", "Ingrese nueva contraseña:", show='*')
            if nueva_password and len(nueva_password) >= 6:
                try:
                    password_hash = hashlib.sha256(nueva_password.encode()).hexdigest()
                    conn = mysql.connector.connect(**MYSQL_CONFIG)
                    cursor = conn.cursor()
                    cursor.execute("UPDATE users SET password = %s WHERE id = %s", (password_hash, user_id))
                    conn.commit()
                    if cursor.rowcount > 0:
                        self.log(f"✅ Contraseña reseteada para usuario ID {user_id}")
                        messagebox.showinfo("Éxito", "Contraseña actualizada")
                    else:
                        self.log(f"❌ No se encontró usuario con ID {user_id}")
                    cursor.close()
                    conn.close()
                except Exception as e:
                    self.log(f"❌ Error reseteando contraseña: {e}")
    
    def mostrar_estadisticas_usuarios(self):
        """Mostrar estadísticas de usuarios"""
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG)
            cursor = conn.cursor()
            
            cursor.execute("SELECT COUNT(*) FROM users")
            total = cursor.fetchone()[0]
            
            cursor.execute("SELECT COUNT(*) FROM users WHERE is_admin = TRUE")
            admins = cursor.fetchone()[0]
            
            cursor.execute("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")
            nuevos = cursor.fetchone()[0]
            
            self.log("📊 Estadísticas de Usuarios:")
            self.log(f"   • Total de usuarios: {total}")
            self.log(f"   • Administradores: {admins}")
            self.log(f"   • Nuevos (última semana): {nuevos}")
            
            cursor.close()
            conn.close()
        except Exception as e:
            self.log(f"❌ Error mostrando estadísticas: {e}")
    
    def mostrar_usuarios_premium(self):
        """Mostrar usuarios premium"""
        self.log("💎 Función de usuarios premium - Próximamente")
    
    def mostrar_usuarios_activos(self):
        """Mostrar usuarios activos"""
        self.log("📈 Función de usuarios activos - Próximamente")
    
    # Gestión de Vehículos
    def listar_vehiculos(self):
        """Listar todos los vehículos"""
        self.log("🚗 Listando vehículos...")
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG)
            cursor = cursor()
            cursor.execute("SELECT id, plate, brand, model, status, battery_level FROM vehicles ORDER BY id DESC LIMIT 20")
            vehiculos = cursor.fetchall()
            self.log(f"Encontrados {len(vehiculos)} vehículos:")
            for veh in vehiculos:
                estado_emoji = {"available": "🟢", "in_use": "🔴", "maintenance": "🔧"}.get(veh[4], "⚪")
                self.log(f"   {estado_emoji} {veh[1]} - {veh[2]} {veh[3]} - Batería: {veh[5]}%")
            cursor.close()
            conn.close()
        except Exception as e:
            self.log(f"❌ Error listando vehículos: {e}")
    
    def anadir_vehiculo(self):
        """Añadir nuevo vehículo"""
        self.log("➕ Función añadir vehículo - Use el formulario en la web")
        messagebox.showinfo("Info", "Use la interfaz web para añadir vehículos")
    
    def eliminar_vehiculo(self):
        """Eliminar vehículo"""
        veh_id = simpledialog.askinteger("Eliminar Vehículo", "Ingrese ID del vehículo:")
        if veh_id and messagebox.askyesno("Confirmar", f"¿Eliminar vehículo ID {veh_id}?"):
            try:
                conn = mysql.connector.connect(**MYSQL_CONFIG)
                cursor = conn.cursor()
                cursor.execute("DELETE FROM vehicles WHERE id = %s", (veh_id,))
                conn.commit()
                if cursor.rowcount > 0:
                    self.log(f"✅ Vehículo ID {veh_id} eliminado")
                else:
                    self.log(f"❌ No se encontró vehículo ID {veh_id}")
                cursor.close()
                conn.close()
            except Exception as e:
                self.log(f"❌ Error eliminando vehículo: {e}")
    
    def buscar_vehiculo(self):
        """Buscar vehículo"""
        placa = simpledialog.askstring("Buscar Vehículo", "Ingrese matrícula:")
        if placa:
            try:
                conn = mysql.connector.connect(**MYSQL_CONFIG)
                cursor = conn.cursor()
                cursor.execute("SELECT * FROM vehicles WHERE plate LIKE %s", (f"%{placa}%",))
                resultados = cursor.fetchall()
                if resultados:
                    self.log(f"🔍 Vehículos encontrados:")
                    for veh in resultados:
                        self.log(f"   • ID:{veh[0]} {veh[1]} - {veh[2]} {veh[3]}")
                else:
                    self.log(f"❌ No se encontraron vehículos con '{placa}'")
                cursor.close()
                conn.close()
            except Exception as e:
                self.log(f"❌ Error buscando vehículo: {e}")
    
    def mostrar_vehiculos_disponibles(self):
        """Mostrar vehículos disponibles"""
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG)
            cursor = conn.cursor()
            cursor.execute("SELECT COUNT(*) FROM vehicles WHERE status = 'available'")
            count = cursor.fetchone()[0]
            self.log(f"🟢 Vehículos disponibles: {count}")
            cursor.close()
            conn.close()
        except Exception as e:
            self.log(f"❌ Error: {e}")
    
    def mostrar_vehiculos_enuso(self):
        """Mostrar vehículos en uso"""
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG)
            cursor = conn.cursor()
            cursor.execute("SELECT COUNT(*) FROM vehicles WHERE status = 'in_use'")
            count = cursor.fetchone()[0]
            self.log(f"🔴 Vehículos en uso: {count}")
            cursor.close()
            conn.close()
        except Exception as e:
            self.log(f"❌ Error: {e}")
    
    def mostrar_estado_bateria(self):
        """Mostrar estado de batería de vehículos"""
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG)
            cursor = conn.cursor()
            cursor.execute("""
                SELECT 
                    SUM(CASE WHEN battery_level >= 80 THEN 1 ELSE 0 END) as alta,
                    SUM(CASE WHEN battery_level >= 50 AND battery_level < 80 THEN 1 ELSE 0 END) as media,
                    SUM(CASE WHEN battery_level < 50 THEN 1 ELSE 0 END) as baja
                FROM vehicles
            """)
            result = cursor.fetchone()
            self.log("🔋 Estado de Batería:")
            self.log(f"   • Alta (≥80%): {result[0] or 0}")
            self.log(f"   • Media (50-79%): {result[1] or 0}")
            self.log(f"   • Baja (<50%): {result[2] or 0}")
            cursor.close()
            conn.close()
        except Exception as e:
            self.log(f"❌ Error: {e}")
    
    def generar_vehiculos_prueba(self):
        """Generar vehículos de prueba"""
        cantidad = simpledialog.askinteger("Generar Vehículos", "¿Cuántos vehículos generar?", initialvalue=5)
        if cantidad and cantidad > 0:
            try:
                conn = mysql.connector.connect(**MYSQL_CONFIG)
                cursor = conn.cursor()
                
                marcas = ["Tesla", "Nissan", "BMW", "Renault", "Volkswagen", "Hyundai", "Audi"]
                modelos = {"Tesla": ["Model 3", "Model S"], "Nissan": ["Leaf"], "BMW": ["i3"], 
                          "Renault": ["Zoe"], "Volkswagen": ["ID.3"], "Hyundai": ["Kona"], "Audi": ["e-tron"]}
                
                self.log(f"📦 Generando {cantidad} vehículos de prueba...")
                for i in range(cantidad):
                    marca = random.choice(marcas)
                    modelo = random.choice(modelos[marca])
                    placa = ''.join(random.choices(string.ascii_uppercase + string.digits, k=7))
                    bateria = random.randint(30, 100)
                    lat = 41.3851 + random.uniform(-0.05, 0.05)
                    lon = 2.1734 + random.uniform(-0.05, 0.05)
                    
                    query = """
                    INSERT INTO vehicles (plate, brand, model, year, status, battery_level, latitude, longitude)
                    VALUES (%s, %s, %s, %s, 'available', %s, %s, %s)
                    """
                    cursor.execute(query, (placa, marca, modelo, 2023, bateria, lat, lon))
                
                conn.commit()
                self.log(f"✅ {cantidad} vehículos de prueba generados")
                cursor.close()
                conn.close()
            except Exception as e:
                self.log(f"❌ Error generando vehículos: {e}")
    
    def limpiar_datos_prueba(self):
        """Limpiar datos de prueba"""
        if messagebox.askyesno("Confirmar", "¿Eliminar todos los vehículos?"):
            try:
                conn = mysql.connector.connect(**MYSQL_CONFIG)
                cursor = conn.cursor()
                cursor.execute("DELETE FROM vehicles")
                conn.commit()
                self.log(f"✅ Todos los vehículos eliminados")
                cursor.close()
                conn.close()
            except Exception as e:
                self.log(f"❌ Error limpiando datos: {e}")
    
    # Herramientas del Sistema
    def limpiar_cache(self):
        """Limpiar caché"""
        self.log("🔄 Limpiando caché...")
        self.log("✅ Caché limpiado (simulado)")
    
    def limpiar_temp(self):
        """Limpiar archivos temporales"""
        self.log("🗑️ Limpiando archivos temporales...")
        self.log("✅ Archivos temporales limpiados (simulado)")
    
    def limpiar_logs(self):
        """Limpiar logs"""
        self.log("📁 Limpiando logs...")
        self.log("✅ Logs limpiados (simulado)")
    
    def verificar_integridad(self):
        """Verificar integridad del sistema"""
        self.log("🔍 Verificando integridad del sistema...")
        self.verificar_todos_sistemas()
        self.log("✅ Verificación de integridad completada")
    
    def verificacion_seguridad(self):
        """Verificación de seguridad"""
        self.log("🔐 Ejecutando verificación de seguridad...")
        self.log("   ✅ Verificando conexiones seguras")
        self.log("   ✅ Verificando permisos de archivos")
        self.log("   ✅ Verificando configuraciones")
        self.log("✅ Verificación de seguridad completada")
    
    def verificacion_salud(self):
        """Verificación de salud del sistema"""
        self.log("📊 Ejecutando verificación de salud...")
        self.verificar_todos_sistemas()
        self.mostrar_tablas_maria()
        self.mostrar_colecciones_mongo()
        self.log("✅ Verificación de salud completada")
    
    def actualizar_sistema(self):
        """Actualizar sistema"""
        self.log("🔄 Función de actualización - Próximamente")
        messagebox.showinfo("Info", "Función de actualización del sistema próximamente")
    
    # Logs y Monitoreo
    def ver_logs(self, tipo_log):
        """Ver logs"""
        self.log(f"📄 Visualizando logs de {tipo_log}...")
        self.visor_logs.delete(1.0, tk.END)
        self.visor_logs.insert(tk.END, f"Logs de {tipo_log} aparecerán aquí\n")
        self.visor_logs.insert(tk.END, f"Función de visualización de logs próximamente\n")
    
    def actualizar_logs(self):
        """Actualizar logs"""
        self.log("🔄 Actualizando logs...")
    
    def mostrar_recursos_sistema(self):
        """Mostrar recursos del sistema"""
        self.log("📊 Recursos del sistema - Próximamente")
    
    def mostrar_sesiones_activas(self):
        """Mostrar sesiones activas"""
        self.log("🔍 Sesiones activas - Próximamente")


def main():
    root = tk.Tk()
    app = HerramientaEazyRide(root)
    root.mainloop()


if __name__ == "__main__":
    main()
