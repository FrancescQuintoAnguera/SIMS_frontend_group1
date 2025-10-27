CREATE TABLE "Tenants" (
  "id" int PRIMARY KEY NOT NULL,
  "legal_name" varchar(50) NOT NULL,
  "cif" varchar(9) NOT NULL,
  "number" varchar(15),
  "addres" varchar(80),
  "created_at" datetime NOT NULL,
  "updated_at" datetime DEFAULT null,
  "deleted_at" datetime DEFAULT null
);

CREATE TABLE "Users" (
  "id" int PRIMARY KEY NOT NULL,
  "id_tenan" int NOT NULL,
  "name" varhcar(30) NOT NULL,
  "surname" varchar(50),
  "dni" varchar(9) NOT NULL,
  "email" varchar(50) NOT NULL,
  "password" varchar(256) NOT NULL,
  "id_role" int NOT NULL,
  "created_at" datetime NOT NULL,
  "updated_at" datetime DEFAULT null,
  "deleted_at" datetime DEFAULT null
);

CREATE TABLE "Roles" (
  "id" int PRIMARY KEY NOT NULL,
  "name" varchar(40) NOT NULL,
  "description" text NOT NULL,
  "created_at" datetime NOT NULL,
  "updated_at" datetime DEFAULT null,
  "deleted_at" datetime DEFAULT null
);

CREATE TABLE "RolePermissions" (
  "id_role" int NOT NULL,
  "id_permission" int NOT NULL
);

CREATE TABLE "Permissions" (
  "id" int PRIMARY KEY NOT NULL,
  "name" varchar(100) NOT NULL,
  "description" text NOT NULL,
  "created_at" datetime NOT NULL,
  "updated_at" datetime DEFAULT null,
  "deleted_at" datetime DEFAULT null
);

CREATE TABLE "Vehicles" (
  "id" int PRIMARY KEY NOT NULL,
  "id_tenan" int NOT NULL,
  "name" varchar(100) NOT NULL,
  "id_type_vehicle" int NOT NULL,
  "status_car" enum(available,disabled,maintenance),
  "vehicle_code" varchar NOT NULL,
  "created_at" datetime NOT NULL,
  "updated_at" datetime DEFAULT null,
  "deleted_at" datetime DEFAULT null
);

CREATE TABLE "Types_vehicles" (
  "id" int PRIMARY KEY NOT NULL,
  "name" varchar(50) NOT NULL,
  "description" text
);

CREATE TABLE "Routes" (
  "id" int PRIMARY KEY NOT NULL,
  "id_user" int NOT NULL,
  "id_vehicle" int NOT NULL,
  "starting_point" float NOT NULL,
  "end_point" float,
  "created_at" datetime NOT NULL,
  "updated_at" datetime DEFAULT null,
  "deleted_at" datetime DEFAULT null
);

CREATE TABLE "Tickets" (
  "id" int PRIMARY KEY NOT NULL,
  "id_tenan" int NOT NULL,
  "id_requester" int NOT NULL,
  "id_assigned" int NOT NULL,
  "id_vehicle" int NOT NULL,
  "description" text,
  "id_satus" int NOT NULL,
  "created_at" datetime NOT NULL,
  "updated_at" datetime DEFAULT null,
  "deleted_at" datetime DEFAULT null
);

CREATE TABLE "Status" (
  "id" int PRIMARY KEY NOT NULL,
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
