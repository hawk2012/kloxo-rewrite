# Kloxo Rewrite

**Kloxo Rewrite** is a community-driven effort to revive and modernize the classic Kloxo hosting control panel, making it compatible with contemporary Linux distributions such as Ubuntu and Debian. This project aims to restore functionality, improve security, and provide a lightweight solution for managing web hosting environments.

## Table of Contents

1. [Overview](#overview)
2. [Project Goals](#project-goals)
3. [System Requirements](#system-requirements)
4. [Installation](#installation)
5. [Configuration](#configuration)
6. [Usage](#usage)
7. [Known Issues](#known-issues)
8. [Disclaimer](#disclaimer)
9. [Contributing](#contributing)
10. [License](#license)

---

## Overview

Kloxo was a popular open-source hosting control panel known for its simplicity and ease of use. However, it became outdated and incompatible with modern systems over time. **Kloxo Rewrite** is an attempt to bring this beloved tool back to life by rewriting and adapting it for modern operating systems like Ubuntu and Debian.

This project is still in development, and while many features are functional, some may not work as expected. Please proceed with caution when using it in production environments.

---

## Project Goals

- **Compatibility**: Ensure Kloxo works seamlessly on Ubuntu and Debian.
- **Modernization**: Update dependencies, libraries, and configurations to align with current standards.
- **Security**: Address vulnerabilities present in the original Kloxo codebase.
- **Extensibility**: Provide a foundation for future enhancements and features.

---

## System Requirements

### Supported Operating Systems
- Ubuntu 20.04 LTS / 22.04 LTS
- Debian 11 / 12

### Minimum Hardware Requirements
- **CPU**: 1 GHz (2+ GHz recommended for production environments)
- **RAM**: 1 GB (2+ GB recommended for production environments)
- **Disk Space**: 10 GB (more recommended for hosting multiple websites)
- **Network**: Stable internet connection

### Software Dependencies
- Apache or Nginx
- MySQL/MariaDB
- PHP 7.4 or higher
- Pure-FTPd (for FTP services)
- BIND (for DNS services)

---

## Installation

### Step 1: Update Your System
Ensure your system is up-to-date:
```bash
sudo apt update && sudo apt upgrade -y
```

### Step 2: Download Kloxo Rewrite
Clone the Kloxo Rewrite repository:
```bash
git clone https://github.com/hawk2012/kloxo-rewrite.git
cd kloxo-rewrite
```

### Step 3: Run the Installer
Execute the installation script:
```bash
sudo bash kloxo-installer.sh
```

Follow the on-screen prompts to complete the installation.

### Step 4: Access the Control Panel
Once installed, access Kloxo Rewrite via:
- **Secure Connection**: `https://<your-server-ip>:7777`
- **Normal Connection**: `http://<your-server-ip>:7778`

Default credentials:
- Username: `admin`
- Password: `admin`

> **Important**: Change the default password after logging in.

---

## Configuration

### Configuring Services
Kloxo Rewrite automatically configures essential services like Apache, MySQL, and BIND. However, you can manually adjust settings in the following files:
- **Pure-FTPd**: `/etc/pure-ftpd.conf`
- **MySQL**: `/etc/mysql/my.cnf`
- **BIND**: `/etc/bind/named.conf`

### Adding Custom Domains
Use the Kloxo Rewrite web interface to add and manage domains. Navigate to **Web > Domains** and follow the prompts.

### Managing Databases
Create and manage MySQL databases via the **Database** section in the control panel.

---

## Usage

### Managing Websites
- Use the **Web** section to create and configure websites.
- Enable/disable SSL/TLS for individual domains.

### Email Management
- Configure email accounts under the **Mail** section.
- Use Postfix and Dovecot for SMTP/IMAP services.

### File Management
- Access the built-in file manager for uploading, editing, and managing files.

### InstallApp
- Install popular PHP applications like WordPress, Joomla, and more with a single click.

---

## Known Issues

- Some legacy features from the original Kloxo may not work correctly.
- Compatibility with certain third-party tools or plugins is not guaranteed.
- The project is still in active development, and bugs may exist.

If you encounter any issues, please report them on the [GitHub Issues](https://github.com/hawk2012/kloxo-rewrite/issues) page.

---

## Disclaimer

**Kloxo Rewrite is a work-in-progress project.** While efforts have been made to ensure compatibility and stability, the software may not function perfectly in all scenarios. Use this software at your own risk, especially in production environments. The developers and contributors are not responsible for any data loss, downtime, or other issues that may arise from using Kloxo Rewrite.

---

## Contributing

We welcome contributions from the community! To contribute:
1. Fork the repository.
2. Create a new branch (`git checkout -b feature/your-feature`).
3. Commit your changes (`git commit -m "Add your feature"`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a pull request.

Please ensure your code adheres to our coding standards and includes appropriate documentation.

---

## License

Kloxo Rewrite is licensed under the **GNU Affero General Public License v3.0**. See the [LICENSE](LICENSE) file for more details.

---

## Support

For support, please visit:
- **Forum**: [Kloxo Rewrite Forum](https://forum.lxcenter.org)
- **GitHub Issues**: [Kloxo Rewrite Issues](https://github.com/hawk2012/kloxo-rewrite/issues)

---

Thank you for your interest in Kloxo Rewrite! We hope this project helps revive the spirit of Kloxo for modern hosting environments.
