# Control Vehicular — Sistema de Tránsito

## Requisitos
- XAMPP (PHP 8+, MySQL/MariaDB)
- Navegador moderno

## Instalación

1. Clona o copia el proyecto en `C:\xampp\htdocs\Transito_Repo`.
2. Importa `schema.sql` en phpMyAdmin o desde la consola MySQL:
   ```sql
   source C:/xampp/htdocs/Transito_Repo/schema.sql
   ```
3. Inicia Apache y MySQL desde el Panel de XAMPP.
4. Abre `http://localhost/Transito_Repo/` en el navegador.

## Credenciales de prueba (administrador)

| Campo   | Valor                  |
|---------|------------------------|
| Correo  | admin@transito.local   |
| Clave   | Admin1234              |

> Solo los **administradores** pueden iniciar sesión en el sistema.

## Roles y permisos

| Acción                                      | Público | Administrador |
|---------------------------------------------|:-------:|:-------------:|
| Consultar tarjeta de circulación (pública)  | ✅      | ✅            |
| Iniciar sesión en el panel                  | ❌      | ✅            |
| Gestionar propietarios / vehículos / pagos  | ❌      | ✅            |
| Renovar tarjeta vencida (pago de placas)    | ❌      | ✅            |

## Flujo de tarjeta de circulación

- Al registrar un **vehículo**, se genera automáticamente su tarjeta con:
  - **Fecha de expedición**: día del registro.
  - **Fecha de vencimiento**: un año después.
- Cada noche (o en cada consulta) el sistema marca como **Vencida** cualquier tarjeta cuya fecha de vencimiento ya pasó.
- Un administrador puede **renovar** la tarjeta desde la pantalla *Tarjetas de circulación*, lo cual registra el pago de placas y extiende la vigencia un año más desde hoy con un nuevo folio.

