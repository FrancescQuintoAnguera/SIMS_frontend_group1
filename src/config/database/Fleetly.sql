CREATE TYPE vehicle_status AS ENUM ('available', 'disabled', 'maintenance');

CREATE TABLE "tenants" (
  "id" SERIAL PRIMARY KEY,
  "legal_name" varchar(50) NOT NULL,
  "cif" varchar(9) NOT NULL,
  "number" varchar(15),
  "addres" varchar(80),
  "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT NULL,
  "deleted_at" TIMESTAMP DEFAULT NULL
);

CREATE TABLE "users" (
  "id" SERIAL PRIMARY KEY,
  "id_tenan" int NOT NULL,
  "name" varchar(30) NOT NULL,
  "surname" varchar(50),
  "dni" varchar(9) NOT NULL,
  "email" varchar(50) NOT NULL,
  "password" varchar(256) NOT NULL,
  "id_role" int NOT NULL,
  "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT NULL,
  "deleted_at" TIMESTAMP DEFAULT NULL
);

CREATE TABLE "roles" (
  "id" SERIAL PRIMARY KEY,
  "name" varchar(40) NOT NULL,
  "description" text NOT NULL,
  "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT NULL,
  "deleted_at" TIMESTAMP DEFAULT NULL
);

CREATE TABLE "role_permissions" (
  "id_role" int NOT NULL,
  "id_permission" int NOT NULL,
  PRIMARY KEY ("id_role", "id_permission")
);

CREATE TABLE "permissions" (
  "id" SERIAL PRIMARY KEY,
  "name" varchar(100) NOT NULL,
  "description" text NOT NULL,
  "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT NULL,
  "deleted_at" TIMESTAMP DEFAULT NULL
);

CREATE TABLE "vehicles" (
  "id" SERIAL PRIMARY KEY,
  "id_tenan" int NOT NULL,
  "name" varchar(100) NOT NULL,
  "id_type_vehicle" int NOT NULL,
  "status_car" vehicle_status NOT NULL DEFAULT 'available',
  "vehicle_code" varchar(50) NOT NULL,
  "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT NULL,
  "deleted_at" TIMESTAMP DEFAULT NULL
);

CREATE TABLE "types_vehicles" (
  "id" SERIAL PRIMARY KEY,
  "name" varchar(50) NOT NULL,
  "description" text
);

CREATE TABLE "routes" (
  "id" SERIAL PRIMARY KEY,
  "id_user" int NOT NULL,
  "id_vehicle" int NOT NULL,
  "starting_point" float NOT NULL,
  "end_point" float,
  "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT NULL,
  "deleted_at" TIMESTAMP DEFAULT NULL
);

CREATE TABLE "tickets" (
  "id" SERIAL PRIMARY KEY,
  "id_tenan" int NOT NULL,
  "id_requester" int NOT NULL,
  "id_assigned" int NOT NULL,
  "id_vehicle" int NOT NULL,
  "description" text,
  "id_satus" int NOT NULL,
  "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT NULL,
  "deleted_at" TIMESTAMP DEFAULT NULL
);

CREATE TABLE "status" (
  "id" SERIAL PRIMARY KEY,
  "name" varchar(30) NOT NULL
);

ALTER TABLE "Users" ADD FOREIGN KEY ("id_tenan") REFERENCES "Tenants" ("id");

ALTER TABLE "Users" ADD FOREIGN KEY ("id_role") REFERENCES "Roles" ("id");

ALTER TABLE "RolePermissions" ADD FOREIGN KEY ("id_role") REFERENCES "Roles" ("id");

ALTER TABLE "RolePermissions" ADD FOREIGN KEY ("id_permission") REFERENCES "Permissions" ("id");

ALTER TABLE "Vehicles" ADD FOREIGN KEY ("id_tenan") REFERENCES "Tenants" ("id");

ALTER TABLE "Vehicles" ADD FOREIGN KEY ("id_type_vehicle") REFERENCES "Types_vehicles" ("id");

ALTER TABLE "Routes" ADD FOREIGN KEY ("id_vehicle") REFERENCES "Vehicles" ("id");

ALTER TABLE "Routes" ADD FOREIGN KEY ("id_user") REFERENCES "Users" ("id");

ALTER TABLE "Tickets" ADD FOREIGN KEY ("id_satus") REFERENCES "Status" ("id");

ALTER TABLE "Tickets" ADD FOREIGN KEY ("id_tenan") REFERENCES "Tenants" ("id");

ALTER TABLE "Tickets" ADD FOREIGN KEY ("id_requester") REFERENCES "Users" ("id");

ALTER TABLE "Tickets" ADD FOREIGN KEY ("id_assigned") REFERENCES "Users" ("id");

ALTER TABLE "Tickets" ADD FOREIGN KEY ("id_vehicle") REFERENCES "Vehicles" ("id");
