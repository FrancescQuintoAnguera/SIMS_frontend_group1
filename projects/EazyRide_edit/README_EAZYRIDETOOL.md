# EazyRide Administration Tool

Complete GUI administration tool for managing the EazyRide vehicle sharing application.

## Features

### 📊 Database Management
- **MariaDB Operations**
  - Create/Drop database
  - Create table structure
  - View tables and statistics
  - Database health monitoring
  
- **MongoDB Operations**
  - Create/Drop database
  - Create collections
  - View collections and statistics
  - Document management

- **Quick Actions**
  - Initialize all databases
  - Reset all databases
  - Backup/Restore (coming soon)

### 🧪 Server Testing
- **API Endpoint Testing**
  - Authentication API
  - Profile API
  - Vehicles API
  - Booking API
  - Payment API
  - Run all tests

- **Server Tests**
  - Web server health check
  - Database connection test
  - PHP session testing
  - File permissions check
  - Comprehensive server tests

- **Performance Testing**
  - Load testing
  - Response time analysis
  - Database performance metrics

### 👥 User Management
- **User Administration**
  - Create admin users
  - List all users
  - Search users
  - Edit user details
  - Delete users
  - Reset passwords

- **User Statistics**
  - User statistics dashboard
  - Premium users list
  - Active users monitoring
  - New users tracking

### 🚗 Vehicle Management
- **Vehicle Operations**
  - List all vehicles
  - Add new vehicles
  - Edit vehicle details
  - Delete vehicles
  - Search vehicles

- **Vehicle Status**
  - Available vehicles
  - In-use vehicles
  - Under maintenance
  - Battery status monitoring
  - Vehicle location tracking

- **Test Data**
  - Generate mock vehicles
  - Clear test data

### 🛠️ System Tools
- **System Maintenance**
  - Clear cache
  - Clean temporary files
  - Clean log files
  - Check system integrity
  - Repair system

- **Development Tools**
  - Compile CSS
  - Minify JavaScript
  - Optimize images
  - Generate documentation

- **System Monitoring**
  - Update system
  - Security check
  - Health check

### 📝 Logs & Monitoring
- **Log Viewer**
  - PHP error logs
  - Apache logs
  - MySQL logs
  - Access logs

- **Real-time Monitoring**
  - System resources
  - Active sessions
  - Real-time statistics

## Installation

### Requirements
```bash
pip install mysql-connector-python pymongo requests tkinter
```

### Configuration

1. Make sure you have a `.env` file in the project root with:
```env
# MariaDB Configuration
DB_USER=your_db_user
DB_PASS=your_db_password
DB_NAME=eazyride

# MongoDB Configuration
MONGO_INITDB_ROOT_USERNAME=your_mongo_user
MONGO_INITDB_ROOT_PASSWORD=your_mongo_password
MONGO_INITDB_DATABASE=eazyride

# Web Server
WEB_URL=http://localhost:8000
```

## Usage

### Run the Tool
```bash
python3 eazyridetool.py
```

### Quick Start

1. **Check System Status**
   - Click "🔄 Refresh Status" to check all connections

2. **Initialize Database**
   - Go to "📊 Database" tab
   - Click "🚀 Initialize All" to create all databases

3. **Create Admin User**
   - Go to "👥 Users" tab
   - Fill in the form and click "✅ Create Admin"

4. **Run Tests**
   - Go to "🧪 Server Tests" tab
   - Click "🎯 Run All API Tests"

## Features by Tab

### Database Tab
- Create and manage MariaDB and MongoDB databases
- View database statistics
- Quick initialization and reset options
- Backup and restore capabilities

### Server Tests Tab
- Comprehensive API testing
- Server health monitoring
- Performance benchmarking
- Automated test suites

### Users Tab
- Complete user management
- Admin user creation
- User statistics and analytics
- Password management

### Vehicles Tab
- Full vehicle fleet management
- Status monitoring
- Location tracking
- Test data generation

### Tools Tab
- System maintenance utilities
- Development tools
- Security checks
- Health monitoring

### Logs Tab
- Multi-source log viewing
- Real-time log monitoring
- System resource tracking
- Session management

## Console Output

All operations are logged to the console with:
- ✅ Success messages (green)
- ❌ Error messages (red)
- ⚠️ Warning messages (orange)
- ℹ️ Info messages (blue)
- Timestamps for all operations

### Console Features
- **Clear**: Clear console output
- **Save Log**: Export console to text file
- **Export Report**: Generate HTML report

## System Status Indicators

The tool shows real-time status for:
- **MariaDB**: Connection status with color coding
- **MongoDB**: Connection status with color coding
- **Web Server**: HTTP response status

Status Colors:
- 🟢 Green: Connected/Running
- 🟡 Orange: Warning/Not configured
- 🔴 Red: Error/Not running

## Architecture

```
eazyridetool.py
├── Database Management
│   ├── MariaDB Operations
│   └── MongoDB Operations
├── Server Testing
│   ├── API Tests
│   ├── Server Tests
│   └── Performance Tests
├── User Management
│   ├── User CRUD
│   └── Statistics
├── Vehicle Management
│   ├── Vehicle CRUD
│   └── Status Monitoring
├── System Tools
│   ├── Maintenance
│   ├── Development
│   └── Monitoring
└── Logs & Monitoring
    ├── Log Viewer
    └── Real-time Stats
```

## Development

### Adding New Features

The tool is organized with separate methods for each feature:

```python
def your_new_feature(self):
    """Your feature description"""
    self.log("🚀 Starting your feature...")
    try:
        # Your implementation
        self.log("✅ Feature completed")
    except Exception as e:
        self.log(f"❌ Error: {e}")
```

### Threading for Long Operations

For long-running operations, use threading:

```python
def long_operation(self):
    threading.Thread(target=self._long_operation_thread, daemon=True).start()

def _long_operation_thread(self):
    # Implementation
    pass
```

## Troubleshooting

### Connection Issues

**MariaDB not connecting:**
- Check if MariaDB is running: `mysql -u root -p`
- Verify `.env` credentials
- Check port 3306 is available

**MongoDB not connecting:**
- Check if MongoDB is running: `mongosh`
- Verify `.env` credentials
- Check port 27017 is available

**Web server not responding:**
- Check if web server is running
- Verify WEB_URL in `.env`
- Check port availability

### Common Errors

**ModuleNotFoundError:**
```bash
pip install mysql-connector-python pymongo requests
```

**Permission Denied:**
- Check database user permissions
- Verify file system permissions

**Database Already Exists:**
- Use "Drop Database" first
- Or use "Reset All" for complete reset

## Security Notes

- Admin passwords are hashed using SHA-256
- Database credentials stored in `.env` file
- `.env` file should be in `.gitignore`
- Never commit credentials to version control

## Version History

### v1.0.0 (Current)
- Complete GUI redesign
- Database management
- Server testing suite
- User management
- Vehicle management
- System tools
- Logs and monitoring
- Dark theme console
- Export capabilities

## Future Features

- [ ] Advanced backup/restore
- [ ] Database migration tools
- [ ] Automated testing scheduler
- [ ] Email notifications
- [ ] Real-time dashboard
- [ ] Performance graphs
- [ ] Log analysis tools
- [ ] Configuration manager
- [ ] Plugin system
- [ ] Multi-language support

## Support

For issues or feature requests, check the project documentation or contact the development team.

## License

Part of the EazyRide project. All rights reserved.
