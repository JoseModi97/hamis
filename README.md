# HAMIS

HAMIS (Halls Accommodation Management Information System) is a PHP application used to manage student hostel bookings and related invoices. It integrates with the Student Management Information System (SMIS) of the University of Nairobi and uses several third party libraries such as FPDF, ADOdb and Smarty.

## Setup

1. Clone or copy the repository into a directory served by your web server.
2. Ensure PHP 8.1.25 or later is installed with required extensions (`mysqli`, `oci8`).
3. Edit configuration values in the PHP scripts as needed. In particular `smis_class_inc.php` contains database connection details.
4. Configure your HTTP server (Apache/Nginx) to point to this directory.

## Usage

After configuration, access pages like `apply_for_room.php` or `bookroom.php` using a browser. Students authenticate with their registration number and password. The system then allows them to apply for accommodation, view allocations and generate invoice PDFs.

## Disclaimers

- **Credentials**: Database credentials for SMIS and HAMIS are required. The example configuration in `smis_class_inc.php` includes placeholders that must be replaced with valid credentials.
- **External Dependencies**: This project depends on external libraries (FPDF, ADOdb, Smarty) which are included in the repository. Ensure the necessary PHP extensions are enabled.

## License

This project is released under the MIT License. See the [LICENSE](LICENSE) file for more information.
