# Hostel Administration Management Information System (HAMIS)

HAMIS is a PHP-based system used by the University of Nairobi for handling hostel accommodation applications, room bookings and related invoicing tasks. This repository contains a snapshot of the PHP code, templates and data files used to run the application.

## Setup

1. **Requirements**
   - PHP 7.x or later with MySQL extensions.
   - A MySQL database server (and optionally Oracle if required by your environment).
   - The [ADODB](https://adodb.sourceforge.net/) library for database access.
   - [FPDF](https://www.fpdf.org/) for PDF generation and other third‑party libraries included in `zephyr/thirdparty`.

2. **Installation**
   - Clone or extract this repository to a directory served by your web server.
   - Configure database credentials in `smis_class_inc.php` and related files. Sample credentials are present in the source but will not work in your environment.
   - Ensure the ADODB and FPDF libraries are available and update the include paths if necessary.
   - Set up MySQL (and Oracle if needed) databases using your own credentials and schema. The repository does not contain database dumps.

3. **Running**
   - Point your web browser to `bookroom.php` or `apply_for_room.php` once the server is configured.
   - Generated invoice text files can be found under the `invoices/` directory, while PDFs are produced via `generate_invoice_pdf.php`.

## Usage

The application allows students to log in, apply for hostel accommodation and print invoices. Administrators manage room allocations and billing through the same interface. Because this is a legacy code base, many configuration values (such as database connections) are hardcoded and need to be changed for your local deployment.

## Disclaimer

This project depends on external libraries (ADODB, FPDF and others) and database credentials that are **not** provided. Before running HAMIS you must supply valid database credentials and verify that all required PHP extensions and libraries are installed. The repository is offered as‑is with no warranty; use at your own risk.

## License

This project is licensed under the terms of the MIT License. See the [LICENSE](LICENSE) file for details.
