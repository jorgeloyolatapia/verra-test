# verra-test

## Qué tiene

- `php bin/console app:wordlist wordlist.txt`: palabras de 6 letras que son la unión de otras dos de la lista.
- `POST /api/fraud-detection`: recibe `{"orders": [...]}` y devuelve `{"fraudulentOrders": [ids]}`.
- `GET /api/doc.json`: OpenAPI del endpoint.
- Ejemplo para probar en `examples/fraud-detection-request.json`.
- Tests con `php bin/phpunit`.

## Supuestos

- Una orden es fraudulenta si otra del mismo `dealId` tiene la misma identidad (mismo email o misma dirección) pero distinta tarjeta.
- Dirección = calle, ciudad, estado y zip iguales, sin mirar el email.
- Si un grupo tiene más de una tarjeta, se marcan todas sus órdenes, también las que comparten tarjeta entre sí.
- Normalizo antes de comparar:
  - email: minúsculas, sin puntos en la parte local y sin `+tag`.
  - dirección: minúsculas, sin `.` ni `,`, `st` → `street`, `rd` → `road` y estados abreviados (`illinois` → `il`, `california` → `ca`, `new york` → `ny`).
  - tarjeta: sin espacios ni guiones.
- Órdenes de `dealId` distintos nunca se relacionan.
- No valido el formato del zip ni de la tarjeta, solo que no estén vacíos.
- Máximo 10 000 órdenes por petición.
- Para añadir otra identidad (p. ej. DNI) basta una línea en `Order::getIdentities()`.
