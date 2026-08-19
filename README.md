# Cat Shelter Management Web Application

## Project Overview

This repository contains a full-stack web application developed for the **Web Application Design** course at **Politecnico di Torino** as part of the Management Engineering (L-8) degree programme, A.Y. 2025-2026.

The project simulates the website and management system of a cat shelter called **Il Parco delle Fusa**.

The application allows visitors and registered users to explore the shelter's cats, book visits, register for volunteering shifts and manage their activities. It also includes administrative functionality for monitoring bookings, volunteering activities and adding new cats to the system.

The project was developed using **PHP, MySQL, Vanilla JavaScript and React**, with particular attention to separation of responsibilities between client-side and server-side components, database privileges, input validation, accessibility and responsive design.

---

## Main Features

### Cat Gallery

The `ospiti.php` page contains a dynamic gallery implemented in React.

The application retrieves cat data asynchronously from the PHP backend using `fetch()` and displays the available cats as interactive cards.

Users can:

- browse the cats currently hosted by the shelter;
- search and sort the available cats;
- select one or more cats when booking a visit.

React manages the gallery and the selection state, while the booking form remains outside the React component and is handled using Vanilla JavaScript.

Communication between the two components is implemented through a custom DOM event:

```text
aggiornamentoGattiScelti
```

When the React selection changes, a `CustomEvent` is dispatched on the `document`. Vanilla JavaScript listens for the event and updates the booking form accordingly.

The resulting flow is:

```text
Database
    ↓
PHP / JSON API
    ↓
React
    ↓
CustomEvent
    ↓
Vanilla JavaScript
    ↓
Booking Form
    ↓
PHP
    ↓
Database
```

---

## Visit Booking

Authenticated users can request a visit to meet one or more cats.

Visits can be booked:

- starting from the following day;
- between 10:30 and 17:30.

Selected cats are transferred from the React gallery to the Vanilla JavaScript booking form and submitted to the backend.

The PHP backend validates the request again before storing it in the database.

The visit and the related cat associations are saved inside a database transaction so that the operation is completed atomically.

---

## Volunteering System

Registered users can also reserve volunteering shifts.

The day is divided into three time slots:

```text
Morning      09:00 - 13:00
Afternoon    13:00 - 17:00
Evening      17:00 - 21:00
```

Each time slot accepts a maximum of two volunteers.

Availability is retrieved asynchronously through a PHP endpoint returning JSON data.

When a date is selected, unavailable shifts are automatically disabled in the interface.

The availability check is repeated on the server before saving the reservation.

A database transaction and `SELECT ... FOR UPDATE` are used to prevent two users from simultaneously booking the last available place in the same shift.

---

## Authentication and User Roles

Authentication is implemented using PHP sessions.

After a successful login, the application stores:

- user ID;
- username;
- administrator status.

The session identifier is regenerated after authentication.

Passwords created through the registration page are stored using:

```php
password_hash()
```

and verified during login using:

```php
password_verify()
```

The application distinguishes between:

- standard users;
- administrators.

Administrative privileges cannot be selected during public registration and protected pages verify the user's role on the server side.

---

## Personal Area

Authenticated users have access to a personal area called **Le Mie Attività**.

From this section they can:

- view upcoming visits;
- view upcoming volunteering shifts;
- cancel their own visits;
- cancel their own volunteering shifts.

Cancellation operations verify both the activity identifier and the authenticated user's identifier before modifying the database.

This prevents a user from modifying another user's reservations by manually changing an ID in the request.

---

## Administrator Area

Administrators have access to a dedicated dashboard containing:

- upcoming visits;
- visit history;
- upcoming volunteering shifts;
- volunteering history;
- a link to add new cats to the database.

The cat insertion page is accessible only to administrators and performs both client-side and server-side validation before storing the new record.

---

## Database Access and Privileges

The project uses separate MySQL accounts depending on the operation being performed.

The available profiles are:

### `lecture`

Used for read-only operations.

```text
SELECT
```

### `registrator`

Used only when creating new users.

```text
INSERT on the users table
```

### `modifier`

Used for operations that require data modification.

```text
SELECT
INSERT
UPDATE
DELETE
```

Database configuration is centralized in:

```text
includes/db_config.php
```

This separation ensures that pages only use the privileges required for their specific purpose.

---

## Validation and Security

Client-side validation is implemented using Vanilla JavaScript to provide immediate feedback to users.

All important checks are repeated on the server side before interacting with the database.

The project also uses:

- prepared SQL statements for user-controlled values;
- `htmlspecialchars()` when dynamic values are inserted into HTML;
- `textContent`, `createElement()` and `createTextNode()` for dynamically generated JavaScript content;
- server-side authorization checks;
- PHP sessions for authentication;
- hashed passwords;
- database transactions for multi-step operations;
- limited MySQL accounts with different privileges.

Technical database errors are logged internally while users receive simplified error messages.

---

## Project Structure

The main pages are located in the root directory.

```text
/
├── home.php
├── login.php
├── registrazione.php
├── ospiti.php
├── volontariato.php
├── sostienici.php
├── area_personale.php
├── admin.php
├── inserimento_gatto.php
├── logout.php
│
├── actions/
├── assets/
└── includes/
```

### `actions/`

Contains backend operations and API endpoints, including:

- cat data retrieval;
- visit processing;
- volunteering availability;
- volunteering reservations;
- booking cancellations.

### `includes/`

Contains shared PHP components and configuration files, including:

```text
header.php
footer.php
db_config.php
```

### `assets/`

Contains:

- CSS files;
- JavaScript files;
- React components;
- images and graphical resources.

---

## Technologies

The project uses:

- **PHP**
- **MySQL**
- **HTML5**
- **CSS3**
- **Vanilla JavaScript**
- **React**
- **Fetch API**
- **JSON**

CSS Flexbox, Grid and media queries are used for responsive layouts.

---

## Responsive Design and Accessibility

The website was designed for:

- desktop;
- tablet;
- smartphone.

Responsive behavior is implemented through CSS without using JavaScript to determine the page layout.

Accessibility features include:

- visible keyboard focus using `:focus-visible`;
- form labels;
- `fieldset` and `legend` elements;
- appropriate alternative text for images;
- keyboard-accessible cat cards;
- `aria-live` regions for important dynamic messages;
- support for `prefers-reduced-motion`.

A dedicated print layout is also provided through:

```css
@media print
```

Interactive and decorative elements are removed or reorganized when pages are printed.

---

## Performance and Technical SEO

The project includes several basic performance optimizations:

- WebP images;
- responsive image sources;
- explicit image dimensions;
- lazy loading where appropriate;
- high-priority loading for important above-the-fold images.

Technical SEO considerations include:

- document language;
- meta descriptions;
- semantic HTML;
- heading hierarchy;
- descriptive alternative text;
- responsive design.

The project intentionally keeps the original CSS and JavaScript files readable rather than minifying them, since the source code is part of the academic submission.

---

## Additional Pages

The project also includes a **Sostienici** section containing:

- distance adoption information;
- donation information;
- contact details;
- FAQ;
- an IBAN copy function.

Privacy Policy, Cookie Policy, Terms and Conditions and association information are also included to reproduce the structure of a realistic website.

---

## Design Considerations

The application follows a clear separation of responsibilities:

```text
PHP
→ sessions, database access and server-side validation

React
→ dynamic cat gallery, searching, sorting and selection

Vanilla JavaScript
→ forms, validation and client-side interactions

MySQL
→ persistent application data
```

The project is intended as an academic implementation rather than a production-ready shelter management platform.

Possible future developments could include:

- complete visit calendar management;
- user notifications;
- cat adoption status management;
- individual photo uploads;
- additional administrator functionality.
