db = db.getSiblingDB('simsdb');

db.cars.drop();
db.history.drop();
db.sensors.drop();
db.logs.drop();

db.createCollection('cars');
db.cars.createIndex({ "license_plate": 1 }, { unique: true });
db.cars.createIndex({ "status": 1 });
db.cars.createIndex({ "location": "2dsphere" });

db.createCollection('history');
db.history.createIndex({ "user_id": 1 });
db.history.createIndex({ "car_id": 1 });
db.history.createIndex({ "date": -1 });
db.history.createIndex({ "action_type": 1 });

db.createCollection('sensors');
db.sensors.createIndex({ "car_id": 1 });
db.sensors.createIndex({ "sensor_id": 1 });
db.sensors.createIndex({ "timestamp": -1 });
db.sensors.createIndex({ "car_id": 1, "timestamp": -1 });

db.createCollection('logs');
db.logs.createIndex({ "car_id": 1 });
db.logs.createIndex({ "timestamp": -1 });
db.logs.createIndex({ "level": 1 });
db.logs.createIndex({ "car_id": 1, "timestamp": -1 });

db.cars.insertMany([
    {
        license_plate: "1234ABC",
        brand: "Tesla",
        model: "Model 3",
        year: 2023,
        status: "available",
        battery_level: 85,
        location: {
            type: "Point",
            coordinates: [2.17009700, 41.38706100]
        },
        location_name: "Plaça Catalunya",
        last_updated: new Date()
    },
    {
        license_plate: "5678DEF",
        brand: "Nissan",
        model: "Leaf",
        year: 2022,
        status: "available",
        battery_level: 92,
        location: {
            type: "Point",
            coordinates: [2.17432500, 41.40362400]
        },
        location_name: "Sagrada Família",
        last_updated: new Date()
    },
    {
        license_plate: "9012GHI",
        brand: "Renault",
        model: "Zoe",
        year: 2023,
        status: "in_use",
        battery_level: 67,
        location: {
            type: "Point",
            coordinates: [2.15268900, 41.41449500]
        },
        location_name: "Park Güell",
        last_updated: new Date()
    },
    {
        license_plate: "3456JKL",
        brand: "BMW",
        model: "i3",
        year: 2022,
        status: "available",
        battery_level: 78,
        location: {
            type: "Point",
            coordinates: [2.12282700, 41.38087900]
        },
        location_name: "Camp Nou",
        last_updated: new Date()
    },
    {
        license_plate: "7890MNO",
        brand: "Volkswagen",
        model: "ID.3",
        year: 2023,
        status: "charging",
        battery_level: 45,
        location: {
            type: "Point",
            coordinates: [2.18966700, 41.37545400]
        },
        location_name: "Barceloneta Beach",
        last_updated: new Date()
    }
]);

db.sensors.insertMany([
    {
        car_id: "1234ABC",
        sensor_id: "battery",
        value: 85,
        unit: "percent",
        timestamp: new Date(),
        status: "normal"
    },
    {
        car_id: "1234ABC",
        sensor_id: "temperature",
        value: 22,
        unit: "celsius",
        timestamp: new Date(),
        status: "normal"
    },
    {
        car_id: "5678DEF",
        sensor_id: "battery",
        value: 92,
        unit: "percent",
        timestamp: new Date(),
        status: "normal"
    },
    {
        car_id: "9012GHI",
        sensor_id: "battery",
        value: 67,
        unit: "percent",
        timestamp: new Date(),
        status: "normal"
    }
]);

db.logs.insertMany([
    {
        car_id: "1234ABC",
        level: "info",
        message: "Vehicle parked at Plaça Catalunya",
        timestamp: new Date(),
        metadata: {
            location: "Plaça Catalunya",
            battery: 85
        }
    },
    {
        car_id: "9012GHI",
        level: "info",
        message: "Vehicle rental started",
        timestamp: new Date(),
        metadata: {
            user_id: 1,
            location: "Park Güell"
        }
    },
    {
        car_id: "7890MNO",
        level: "info",
        message: "Charging started",
        timestamp: new Date(),
        metadata: {
            battery_level: 45,
            location: "Barceloneta Beach"
        }
    }
]);

print("MongoDB database initialized successfully!");
print("Collections created: cars, history, sensors, logs");
print("Sample data inserted for 5 vehicles");
