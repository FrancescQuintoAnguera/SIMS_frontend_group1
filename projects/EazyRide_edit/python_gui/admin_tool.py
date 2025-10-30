#!/usr/bin/env python3
"""
VoltiaCar Database Administration GUI
A Tkinter-based GUI wrapper for the database.py CLI tool
"""

import tkinter as tk
from tkinter import ttk, scrolledtext, messagebox
import mysql.connector
from pymongo import MongoClient
import sys
import os
from pathlib import Path


env_file = Path(__file__).parent.parent / '.env'
if env_file.exists():
    with open(env_file) as f:
        for line in f:
            line = line.strip()
            if line and not line.startswith('#') and '=' in line:
                key, value = line.split('=', 1)
                if key not in os.environ:
                    os.environ[key] = value

# MongoDB Configuration
MONGO_USER = os.getenv('MONGO_INITDB_ROOT_USERNAME')
MONGO_PASS = os.getenv('MONGO_INITDB_ROOT_PASSWORD')
MONGO_HOST = 'localhost'
MONGO_PORT = '27017'
MONGO_DB = os.getenv('MONGO_INITDB_DATABASE')

MONGO_URI = ( f"mongodb://{MONGO_USER}:{MONGO_PASS}@{MONGO_HOST}:{MONGO_PORT}/?authSource=admin" if all([MONGO_USER, MONGO_PASS]) else None )

# MariaDB Configuration
DB_HOST = 'localhost'
DB_USER = os.getenv('DB_USER')
DB_PASS = os.getenv('DB_PASS')
DB_NAME = os.getenv('DB_NAME')

MYSQL_CONFIG = {
    "host": DB_HOST,
    "user": DB_USER,
    "password": DB_PASS,
    "database": DB_NAME
} if all([DB_USER, DB_PASS]) else None

MYSQL_CONFIG_NO_DB = {
    "host": DB_HOST,
    "user": DB_USER,
    "password": DB_PASS
} if all([DB_USER, DB_PASS]) else None

class DatabaseAdminGUI:
    def __init__(self, root):
        self.root = root
        self.root.title("VoltiaCar Database Administration")
        self.root.geometry("900x700")
        
        # Configure style
        style = ttk.Style()
        style.theme_use('clam')
        
        # Create main container
        main_frame = ttk.Frame(root, padding="10")
        main_frame.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S))
        
        # Configure grid weights
        root.columnconfigure(0, weight=1)
        root.rowconfigure(0, weight=1)
        main_frame.columnconfigure(0, weight=1)
        main_frame.rowconfigure(2, weight=1)
        
        # Title
        title_label = ttk.Label(main_frame, text="VoltiaCar Database Administration", 
                                font=('Arial', 16, 'bold'))
        title_label.grid(row=0, column=0, pady=10)
        
        # Connection Status Frame
        status_frame = ttk.LabelFrame(main_frame, text="Connection Status", padding="10")
        status_frame.grid(row=1, column=0, sticky=(tk.W, tk.E), pady=5)
        
        self.mariadb_status = ttk.Label(status_frame, text="MariaDB: Not checked", 
                                        foreground="gray")
        self.mariadb_status.grid(row=0, column=0, padx=10)
        
        self.mongodb_status = ttk.Label(status_frame, text="MongoDB: Not checked", 
                                        foreground="gray")
        self.mongodb_status.grid(row=0, column=1, padx=10)
        
        ttk.Button(status_frame, text="Check Connections", 
                  command=self.check_connections).grid(row=0, column=2, padx=10)
        
        # Notebook for different sections
        notebook = ttk.Notebook(main_frame)
        notebook.grid(row=2, column=0, sticky=(tk.W, tk.E, tk.N, tk.S), pady=5)
        
        # Tab 1: MariaDB Operations
        mariadb_frame = ttk.Frame(notebook, padding="10")
        notebook.add(mariadb_frame, text="MariaDB Operations")
        self.create_mariadb_tab(mariadb_frame)
        
        # Tab 2: MongoDB Operations
        mongodb_frame = ttk.Frame(notebook, padding="10")
        notebook.add(mongodb_frame, text="MongoDB Operations")
        self.create_mongodb_tab(mongodb_frame)
        
        # Tab 3: General Operations
        general_frame = ttk.Frame(notebook, padding="10")
        notebook.add(general_frame, text="General Operations")
        self.create_general_tab(general_frame)
        
        # Tab 4: Create Admin User
        admin_frame = ttk.Frame(notebook, padding="10")
        notebook.add(admin_frame, text="Create Admin User")
        self.create_admin_tab(admin_frame)
        
        # Output Text Area
        output_frame = ttk.LabelFrame(main_frame, text="Output Log", padding="5")
        output_frame.grid(row=3, column=0, sticky=(tk.W, tk.E, tk.N, tk.S), pady=5)
        main_frame.rowconfigure(3, weight=1)
        
        self.output_text = scrolledtext.ScrolledText(output_frame, height=10, 
                                                      wrap=tk.WORD, font=('Courier', 9))
        self.output_text.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S))
        output_frame.columnconfigure(0, weight=1)
        output_frame.rowconfigure(0, weight=1)
        
        # Clear button
        ttk.Button(output_frame, text="Clear Log", 
                  command=self.clear_output).grid(row=1, column=0, pady=5)
        
        self.log("Welcome to VoltiaCar Database Administration Tool")
        
        # Check if environment variables are loaded
        if not MYSQL_CONFIG_NO_DB:
            self.log("⚠️  MariaDB environment variables not configured!")
            self.log("   Please check your .env file for DB_USER and DB_PASS")
        
        if not MONGO_URI:
            self.log("⚠️  MongoDB environment variables not configured!")
            self.log("   Please check your .env file for MONGO credentials")
        
        if MYSQL_CONFIG_NO_DB or MONGO_URI:
            self.log("Checking database connections...")
            self.root.after(100, self.check_connections)  # Check after GUI loads
        else:
            self.log("⚠️  No database credentials found. Operations will not work.")
    
    def create_mariadb_tab(self, parent):
        """Create MariaDB operations tab"""
        ttk.Label(parent, text="MariaDB Database Operations", 
                 font=('Arial', 12, 'bold')).grid(row=0, column=0, columnspan=2, pady=10)
        
        ttk.Button(parent, text="Check MariaDB Connection", 
                  command=self.check_mariadb, width=30).grid(row=1, column=0, pady=5, padx=5)
        
        ttk.Button(parent, text="Create MariaDB Database", 
                  command=self.create_db_maria, width=30).grid(row=2, column=0, pady=5, padx=5)
        
        ttk.Button(parent, text="Create MariaDB Structure", 
                  command=self.create_structure_maria, width=30).grid(row=3, column=0, pady=5, padx=5)
        
        ttk.Button(parent, text="Drop MariaDB Database", 
                  command=self.drop_db_maria, width=30).grid(row=4, column=0, pady=5, padx=5)
        
        # Info text
        info_text = """MariaDB stores:
• users - User accounts and profiles
• nationalities - Country data
• subscriptions - User subscriptions
• vehicles - Vehicle information
• locations - Parking locations
• vehicle_usage - Usage history
• payments - Payment records"""
        
        info_label = ttk.Label(parent, text=info_text, justify=tk.LEFT, 
                              foreground="blue", font=('Arial', 9))
        info_label.grid(row=5, column=0, pady=20, padx=10)
    
    def create_mongodb_tab(self, parent):
        """Create MongoDB operations tab"""
        ttk.Label(parent, text="MongoDB Database Operations", 
                 font=('Arial', 12, 'bold')).grid(row=0, column=0, columnspan=2, pady=10)
        
        ttk.Button(parent, text="Check MongoDB Connection", 
                  command=self.check_mongodb, width=30).grid(row=1, column=0, pady=5, padx=5)
        
        ttk.Button(parent, text="Create MongoDB Database", 
                  command=self.create_db_mongo, width=30).grid(row=2, column=0, pady=5, padx=5)
        
        ttk.Button(parent, text="Create MongoDB Structure", 
                  command=self.create_structure_mongo, width=30).grid(row=3, column=0, pady=5, padx=5)
        
        ttk.Button(parent, text="Drop MongoDB Database", 
                  command=self.drop_db_mongo, width=30).grid(row=4, column=0, pady=5, padx=5)
        
        # Info text
        info_text = """MongoDB stores:
• cars - Vehicle real-time data
• history - Actions, trips, claims
• sensors - Vehicle sensor data
• logs - System logs and events"""
        
        info_label = ttk.Label(parent, text=info_text, justify=tk.LEFT, 
                              foreground="blue", font=('Arial', 9))
        info_label.grid(row=5, column=0, pady=20, padx=10)
    
    def create_general_tab(self, parent):
        """Create general operations tab"""
        ttk.Label(parent, text="General Database Operations", 
                 font=('Arial', 12, 'bold')).grid(row=0, column=0, columnspan=2, pady=10)
        
        ttk.Button(parent, text="Create ALL Databases and Structures", 
                  command=self.create_all_databases, width=40).grid(row=1, column=0, pady=10, padx=5)
        
        ttk.Button(parent, text="Drop ALL Databases", 
                  command=self.drop_all_databases, width=40).grid(row=2, column=0, pady=10, padx=5)
        
        # Warning text
        warning_text = """⚠️  WARNING: 
Dropping databases will permanently delete all data!
This action cannot be undone."""
        
        warning_label = ttk.Label(parent, text=warning_text, justify=tk.LEFT, 
                                 foreground="red", font=('Arial', 10, 'bold'))
        warning_label.grid(row=3, column=0, pady=20, padx=10)
    
    def create_admin_tab(self, parent):
        """Create admin user creation tab"""
        ttk.Label(parent, text="Create Administrator User", 
                 font=('Arial', 12, 'bold')).grid(row=0, column=0, columnspan=2, pady=10)
        
        # Username
        ttk.Label(parent, text="Username:").grid(row=1, column=0, sticky=tk.E, padx=5, pady=5)
        self.admin_username = ttk.Entry(parent, width=30)
        self.admin_username.grid(row=1, column=1, padx=5, pady=5)
        
        # Email
        ttk.Label(parent, text="Email:").grid(row=2, column=0, sticky=tk.E, padx=5, pady=5)
        self.admin_email = ttk.Entry(parent, width=30)
        self.admin_email.grid(row=2, column=1, padx=5, pady=5)
        
        # Password
        ttk.Label(parent, text="Password:").grid(row=3, column=0, sticky=tk.E, padx=5, pady=5)
        self.admin_password = ttk.Entry(parent, width=30, show="*")
        self.admin_password.grid(row=3, column=1, padx=5, pady=5)
        
        # Confirm Password
        ttk.Label(parent, text="Confirm Password:").grid(row=4, column=0, sticky=tk.E, padx=5, pady=5)
        self.admin_password_confirm = ttk.Entry(parent, width=30, show="*")
        self.admin_password_confirm.grid(row=4, column=1, padx=5, pady=5)
        
        # Create button
        ttk.Button(parent, text="Create Admin User", 
                  command=self.create_admin_user, width=30).grid(row=5, column=0, columnspan=2, pady=20)
        
        # Info text
        info_text = """This will create a user with administrator privileges.
The user will have is_admin=1 in the database.
Password will be hashed using bcrypt."""
        
        info_label = ttk.Label(parent, text=info_text, justify=tk.LEFT, 
                              foreground="blue", font=('Arial', 9))
        info_label.grid(row=6, column=0, columnspan=2, pady=10, padx=10)
    
    def log(self, message):
        """Add message to output log"""
        self.output_text.insert(tk.END, message + "\n")
        self.output_text.see(tk.END)
        self.root.update_idletasks()
    
    def clear_output(self):
        """Clear output log"""
        self.output_text.delete(1.0, tk.END)
    
    def check_connections(self):
        """Check both database connections"""
        self.check_mariadb()
        self.check_mongodb()
    
    def check_mariadb(self):
        """Check MariaDB connection"""
        if not MYSQL_CONFIG_NO_DB:
            self.mariadb_status.config(text="MariaDB: Not configured ⚠️", foreground="orange")
            self.log("⚠️  MariaDB environment variables not set. Please configure .env file.")
            return
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG_NO_DB)
            conn.close()
            self.mariadb_status.config(text="MariaDB: Connected ✅", foreground="green")
            self.log("✅ Successful connection to MariaDB.")
        except Exception as e:
            self.mariadb_status.config(text="MariaDB: Error ❌", foreground="red")
            self.log(f"❌ Connection error to MariaDB: {e}")
    
    def check_mongodb(self):
        """Check MongoDB connection"""
        if not MONGO_URI:
            self.mongodb_status.config(text="MongoDB: Not configured ⚠️", foreground="orange")
            self.log("⚠️  MongoDB environment variables not set. Please configure .env file.")
            return
        try:
            client = MongoClient(MONGO_URI)
            client.admin.command("ping")
            self.mongodb_status.config(text="MongoDB: Connected ✅", foreground="green")
            self.log("✅ Successful connection to MongoDB.")
        except Exception as e:
            self.mongodb_status.config(text="MongoDB: Error ❌", foreground="red")
            self.log(f"❌ Connection error to MongoDB: {e}")
    
    def create_db_maria(self):
        """Create MariaDB database"""
        if not MYSQL_CONFIG_NO_DB:
            self.log("⚠️  MariaDB not configured. Please set environment variables.")
            return
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG_NO_DB)
            cursor = conn.cursor()
            cursor.execute(f"CREATE DATABASE IF NOT EXISTS {DB_NAME}")
            self.log(f"✅ MariaDB database created/ready.")
            cursor.close()
            conn.close()
        except Exception as e:
            self.log(f"❌ Error creating MariaDB DB: {e}")
            cursor.close()
            conn.close()

    def drop_db_maria(self):
        """Drop MariaDB database"""
        if messagebox.askyesno("Confirm", f"Are you sure you want to drop the database?\nThis will delete all data!"):
            try:
                conn = mysql.connector.connect(**MYSQL_CONFIG_NO_DB)
                cursor = conn.cursor()
                cursor.execute(f"DROP DATABASE IF EXISTS {DB_NAME}")
                cursor.close()
                conn.close()
            except Exception as e:
                self.log(f"❌ Error dropping MariaDB DB: {e}")
    
    def create_structure_maria(self):
        """Create MariaDB table structure"""
        try:
            conn = mysql.connector.connect(**MYSQL_CONFIG)
            cursor = conn.cursor()
            
            # Drop tables if they exist
            cursor.execute("SET FOREIGN_KEY_CHECKS = 0;")
            cursor.execute("DROP TABLE IF EXISTS payments;")
            cursor.execute("DROP TABLE IF EXISTS vehicle_usage;")
            cursor.execute("DROP TABLE IF EXISTS subscriptions;")
            cursor.execute("DROP TABLE IF EXISTS vehicles;")
            cursor.execute("DROP TABLE IF EXISTS locations;")
            cursor.execute("DROP TABLE IF EXISTS users;")
            cursor.execute("DROP TABLE IF EXISTS nationalities;")
            cursor.execute("SET FOREIGN_KEY_CHECKS = 1;")
            
            # Create nationalities table
            cursor.execute("""
            CREATE TABLE IF NOT EXISTS nationalities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE
            );
            """)
            
            # Create users table
            cursor.execute("""
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL UNIQUE,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                fullname VARCHAR(50),
                phone VARCHAR(20),
                birth_date DATE,
                sex ENUM('M', 'F', 'O') DEFAULT NULL,  
                address VARCHAR(255),                       
                dni VARCHAR(20),
                is_admin BOOLEAN NOT NULL DEFAULT FALSE,
                iban VARCHAR(34),
                driver_license_photo VARCHAR(255),
                nationality_id INT,
                minute_balance INT DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (nationality_id) REFERENCES nationalities(id)
            );
            """)
            
            # Create subscriptions table
            cursor.execute("""
            CREATE TABLE IF NOT EXISTS subscriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                type ENUM('basic', 'premium') NOT NULL DEFAULT 'basic',
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                free_minutes INT DEFAULT 25,
                unlock_fee_waived BOOLEAN DEFAULT TRUE,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            );
            """)
            
            # Create vehicles table
            cursor.execute("""
            CREATE TABLE IF NOT EXISTS vehicles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                plate VARCHAR(20) NOT NULL UNIQUE,
                brand VARCHAR(50) NOT NULL,
                model VARCHAR(50) NOT NULL,
                year INT,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            """)
            
            # Create locations table
            cursor.execute("""
            CREATE TABLE IF NOT EXISTS locations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                latitude DECIMAL(10,8) NOT NULL,
                longitude DECIMAL(11,8) NOT NULL,
                address VARCHAR(255),
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            """)
            
            # Create vehicle_usage table
            cursor.execute("""
            CREATE TABLE IF NOT EXISTS vehicle_usage (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                vehicle_id INT NOT NULL,
                start_time DATETIME NOT NULL,
                end_time DATETIME,
                start_location_id INT,
                end_location_id INT,
                total_distance_km DECIMAL(8,2),
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
                FOREIGN KEY (start_location_id) REFERENCES locations(id),
                FOREIGN KEY (end_location_id) REFERENCES locations(id)
            );
            """)
            
            # Create payments table
            cursor.execute("""
            CREATE TABLE IF NOT EXISTS payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                vehicle_usage_id INT,
                amount DECIMAL(10,2) NOT NULL,
                payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                type ENUM('unlock', 'time', 'subscription') NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (vehicle_usage_id) REFERENCES vehicle_usage(id)
            );
            """)
            
            conn.commit()
            self.log("✅ Tables structure (users, nationalities, subscriptions, vehicles, locations, vehicle_usage, payments) created in MariaDB.")
            cursor.close()
            conn.close()
        except Exception as e:
            self.log(f"❌ Error creating MariaDB structure: {e}")
    
    def create_db_mongo(self):
        """Create MongoDB database"""
        try:
            client = MongoClient(MONGO_URI)
            db = client[MONGO_DB]
            db.create_collection("dummy", capped=False)
            db["dummy"].drop()
            self.log("✅ MongoDB database ready (created on data insertion).")
        except Exception as e:
            self.log(f"❌ Error creating MongoDB DB: {e}")
    
    def drop_db_mongo(self):
        """Drop MongoDB database"""
        if messagebox.askyesno("Confirm", "Are you sure you want to drop the MongoDB database?\nThis will delete all data!"):
            try:
                client = MongoClient(MONGO_URI)
                client.drop_database(MONGO_DB)
                self.log("✅ MongoDB database dropped.")
            except Exception as e:
                self.log(f"❌ Error dropping MongoDB DB: {e}")
    
    def create_structure_mongo(self):
        """Create MongoDB collection structure"""
        try:
            client = MongoClient(MONGO_URI)
            db = client[MONGO_DB]
            
            # Collection cars
            if "cars" not in db.list_collection_names():
                db.create_collection("cars")
                db["cars"].create_index("_id")
                db["cars"].create_index("license_plate", unique=True)
                db["cars"].create_index("location")                
                db["cars"].create_index("battery_level")
                db["cars"].create_index("status")
                db["cars"].create_index("last_update")
            
            # Collection history
            if "history" not in db.list_collection_names():
                db.create_collection("history")
                db["history"].create_index("user_id")
                db["history"].create_index("car_id")
                db["history"].create_index("date")
            
            # Collection sensors
            if "sensors" not in db.list_collection_names():
                db.create_collection("sensors")
                db["sensors"].create_index("car_id")
                db["sensors"].create_index("sensor_id")
                db["sensors"].create_index("timestamp")
            
            # Collection logs
            if "logs" not in db.list_collection_names():
                db.create_collection("logs")
                db["logs"].create_index("car_id")
                db["logs"].create_index("timestamp")
            
            self.log("✅ Collections (cars, history, sensors, logs) created in MongoDB.")
        except Exception as e:
            if "already exists" in str(e):
                self.log("ℹ️  Collections already exist in MongoDB.")
            else:
                self.log(f"❌ Error creating MongoDB collections: {e}")
    
    def create_all_databases(self):
        """Create all databases and structures"""
        if messagebox.askyesno("Confirm", "Create all databases and structures?"):
            self.create_db_maria()
            self.create_structure_maria()
            self.create_db_mongo()
            self.create_structure_mongo()
            self.log("✅ All databases and structures created.")
    
    def drop_all_databases(self):
        """Drop all databases"""
        if messagebox.askyesno("Confirm", "Are you sure you want to drop ALL databases?\nThis will delete ALL data!"):
            self.drop_db_maria()
            self.drop_db_mongo()
            self.log("✅ All databases dropped.")
    
    def create_admin_user(self):
        """Create an admin user in MariaDB"""
        username = self.admin_username.get().strip()
        email = self.admin_email.get().strip()
        password = self.admin_password.get()
        password_confirm = self.admin_password_confirm.get()
        
        # Validation
        if not username or not email or not password:
            messagebox.showerror("Error", "All fields are required!")
            return
        
        if password != password_confirm:
            messagebox.showerror("Error", "Passwords do not match!")
            return
        
        if len(password) < 6:
            messagebox.showerror("Error", "Password must be at least 6 characters!")
            return
        
        try:
            import hashlib
            # Hash password (simple hash for demo - in production use bcrypt)
            password_hash = hashlib.sha256(password.encode()).hexdigest()
            
            conn = mysql.connector.connect(**MYSQL_CONFIG)
            cursor = conn.cursor()
            
            # Insert admin user
            query = """
            INSERT INTO users (username, email, password, is_admin, created_at)
            VALUES (%s, %s, %s, %s, NOW())
            """
            cursor.execute(query, (username, email, password_hash, True))
            conn.commit()
            
            self.log(f"✅ Admin user '{username}' created successfully!")
            messagebox.showinfo("Success", f"Admin user '{username}' created successfully!")
            
            # Clear fields
            self.admin_username.delete(0, tk.END)
            self.admin_email.delete(0, tk.END)
            self.admin_password.delete(0, tk.END)
            self.admin_password_confirm.delete(0, tk.END)
            
            cursor.close()
            conn.close()
        except mysql.connector.IntegrityError as e:
            if "Duplicate entry" in str(e):
                self.log(f"❌ Error: Username or email already exists!")
                messagebox.showerror("Error", "Username or email already exists!")
            else:
                self.log(f"❌ Database error: {e}")
                messagebox.showerror("Error", f"Database error: {e}")
        except Exception as e:
            self.log(f"❌ Error creating admin user: {e}")
            messagebox.showerror("Error", f"Error creating admin user: {e}")

def main():
    root = tk.Tk()
    app = DatabaseAdminGUI(root)
    root.mainloop()

if __name__ == "__main__":
    main()
