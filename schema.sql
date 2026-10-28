CREATE DATABASE ControlVehicular;
USE ControlVehicular;

CREATE TABLE Usuarios (
    IdUsuario INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Rol VARCHAR(50) NOT NULL
);

CREATE TABLE Propietarios (
    IdPropietario INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Apellido VARCHAR(100) NOT NULL,
    RFC VARCHAR(13) NOT NULL,
    Telefono VARCHAR(15),
    Direccion VARCHAR(255)
);

CREATE TABLE Vehiculos (
    IdVehiculo INT AUTO_INCREMENT PRIMARY KEY,
    Matricula VARCHAR(20) NOT NULL,
    NumeroSerie VARCHAR(50) NOT NULL,
    Marca VARCHAR(50) NOT NULL,
    Modelo VARCHAR(50) NOT NULL,
    Anio INT NOT NULL,
    Color VARCHAR(30),

    IdPropietario INT NOT NULL,

    FOREIGN KEY (IdPropietario)
        REFERENCES Propietarios(IdPropietario)
);

CREATE TABLE Pagos (
    IdPago INT AUTO_INCREMENT PRIMARY KEY,
    FolioPago VARCHAR(30) NOT NULL,
    Monto DECIMAL(10,2) NOT NULL,
    FechaPago DATE NOT NULL,
    Concepto VARCHAR(100) NOT NULL,
    Estatus VARCHAR(30) NOT NULL,

    IdPropietario INT NOT NULL,
    IdVehiculo INT NOT NULL,

    FOREIGN KEY (IdPropietario)
        REFERENCES Propietarios(IdPropietario),

    FOREIGN KEY (IdVehiculo)
        REFERENCES Vehiculos(IdVehiculo)
);

CREATE TABLE TarjetasCirculacion (
    IdTarjeta INT AUTO_INCREMENT PRIMARY KEY,
    NumeroFolio VARCHAR(30) NOT NULL,
    FechaExpedicion DATE NOT NULL,
    FechaVencimiento DATE NOT NULL,
    Estatus VARCHAR(30) NOT NULL,

    IdVehiculo INT NOT NULL,
    IdPropietario INT NOT NULL,

    FOREIGN KEY (IdVehiculo)
        REFERENCES Vehiculos(IdVehiculo),

    FOREIGN KEY (IdPropietario)
        REFERENCES Propietarios(IdPropietario)
);

/* DATOS INGRESADOS DE PRUEBA */

INSERT INTO Propietarios (Nombre, Apellido, RFC, Telefono, Direccion)
VALUES
('Carlos', 'Ramírez López', 'RALC850315ABC', '6561234567', 'Av. Juárez 123'),
('María', 'González Pérez', 'GOPM900720DEF', '6562345678', 'Calle Hidalgo 456'),
('José', 'Hernández Torres', 'HETJ880510GHI', '6563456789', 'Av. Tecnológico 789'),
('Ana', 'Martínez Flores', 'MAFA950225JKL', '6564567890', 'Calle Reforma 321');

INSERT INTO Vehiculos
    (Matricula, NumeroSerie, Marca, Modelo, Anio, Color, IdPropietario)
VALUES
    ('ABC123A', 'SERIE001', 'Nissan', 'Versa', 2020, 'Blanco', 1),
    ('DEF456B', 'SERIE002', 'Toyota', 'Corolla', 2022, 'Gris', 2),
    ('GHI789C', 'SERIE003', 'Honda', 'Civic', 2021, 'Negro', 3),
    ('JKL012D', 'SERIE004', 'Chevrolet', 'Aveo', 2019, 'Rojo', 4);

INSERT INTO Pagos
    (FolioPago, Monto, FechaPago, Concepto, Estatus, IdPropietario, IdVehiculo)
VALUES
    ('PAGO001', 850.00, '2026-10-01', 'Refrendo vehicular', 'Pendiente', 1, 1),
    ('PAGO002', 1200.00, '2026-10-02', 'Control vehicular', 'Pendiente', 2, 2),
    ('PAGO003', 650.00, '2026-10-03', 'Refrendo vehicular', 'Pagado', 3, 3),
    ('PAGO004', 950.00, '2026-10-04', 'Control vehicular', 'Pendiente', 4, 4);