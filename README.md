Proiectul dat conține 3 fișiere PHP și 2 fișiere de configurare(.env.example, .htaccess), alături de setup-ul sql și acest README.

Tehnologiile folosite:
Ca limbaj backend am folosit PHP 7.4++, Bază de date MySQL 5.7++ și format API JSON. De asemenea configurarea a fost realizată și cu XAMPP.

Pași de instalare:
- instalare XAMPP. din acesta, se pornește apache si MySQL.
- în locația folderului XAMPP, se creează un folder api în htdocs.
ca exemplu,
D:\xampp\htdocs\project-api
Avem nevoie de Postman ca să testăm la final API-urile.

Configurare:
Pentru baza de date o deschidem local la adresa:
http://localhost/phpmyadmin
După alegem să creăm o nouă Bază de date cu numele project_test cu utf8mb4_unicode_ci. Importăm fișierul setup.sql și verificăm dacă ne apar 3 tabele categories, products, orders.

Conexiunea Bazei de date e configurată din fișierul database.php.
Pornire server:
În browser se deschide adresa:
http://localhost/project-api/api/products

Exemple de apel:
Dacă vrem să vedem toate produsele alegem GET și introducem adresa următoare.
GET http://localhost/project-api/api/products


1. Putem face căutarea cu filtru:
GET http://localhost/project-api/api/products?category_id=1

2. Dacă avem nevoie de un produs specific:
GET http://localhost/project-api/api/products/1

Ca exemplu, pentru http://localhost/project-api/api/products/3
Vom avea:
{
    "id": 3,
    "name": "Siguranță automată tetrapolară 4P 32A",
    "price": "189.00",
    "stock": 40,
    "category_id": 1,
    "category_name": "Întrerupătoare automate",
    "created_at": "2026-05-08 16:41:51"
}


3. O comandă pentru a scădea stocul produsului:
POST http://localhost/softprim-api/api/orders

Unde introducem în Body
{
  "product_id": 5,
  "quantity": 3,
  "customer_email": "client@exemplu.ro"
}
După rulare ne dă:
{
    "order_id": 3,
    "product_id": 5,
    "quantity": 3,
    "total": 960,
    "created_at": "2026-05-08 22:40:22"
}.
