# Changelog

## [1.0.0] - Initial Release (11.05.2025)

### Added
- **Core Functionality**: Initial rewrite of Kloxo for modern systems like Ubuntu and Debian.
- **Installation Script**: Added `kloxo-installer.sh` for automated installation.
- **Service Configuration**: Support for Apache, Nginx, MySQL/MariaDB, BIND, and Pure-FTPd.
- **Web Interface**: Basic web-based control panel interface.
- **Database Management**: Ability to create and manage MySQL databases via the control panel.
- **Email Server**: Integrated Postfix and Dovecot for email services.
- **DNS Management**: Support for BIND with predefined configurations.
- **InstallApp**: One-click installer for popular PHP applications like WordPress, Joomla, etc.
- **File Manager**: Built-in file manager for managing files directly from the web interface.
- **Pure-FTPd Configuration**: Added `/etc/pure-ftpd.conf` with Kloxo-specific settings.
  - Enabled `ChrootEveryone` to restrict users to their home directories.
  - Configured `AltLog` for logging FTP activity to `/var/log/kloxo/pureftpd.log`.
  - Set `Umask` to `133:022` for secure file permissions.
  - Enabled `AntiWarez` to prevent downloading of unauthorized files.
  - Disabled `AnonymousCanCreateDirs` and `AnonymousCantUpload` for better security.
- **MySQL Setup**: Automated creation of `popuser` and `vpopmail` databases during installation.
- **System Users**: Created system users and groups (`lxlabs`, `nouser`, `nogroup`) for Kloxo operations.
- **Logging Improvements**: Enhanced logging for troubleshooting and monitoring.
- **Security Enhancements**:
  - Enabled SSL/TLS support by default in Pure-FTPd.
  - Added firewall rules for passive FTP ports.
  - Restricted access to sensitive files using `ProhibitDotFilesWrite` and `ProhibitDotFilesRead`.

### Changed
- **Default Ports**: Updated default ports for the web interface (`7777` for HTTPS, `7778` for HTTP).
- **Installation Script**: Improved error handling and added retry logic for package installation.
- **Dependency Management**: Switched to APT repositories for package management on Ubuntu/Debian.
- **Configuration Files**: Updated paths and configurations for Ubuntu/Debian compatibility.
- **Service Permissions**: Ensured proper ownership and permissions for log files and directories.

### Fixed
- **Pure-FTPd Configuration**: Corrected issues with passive port ranges and NAT settings.
- **Database Setup**: Fixed bugs related to MySQL root password resets during installation.
- **Service Restarts**: Improved reliability of service restart commands during installation.
- **Firewall Rules**: Resolved issues with firewall rules blocking necessary ports.
- **File Uploads**: Fixed bugs related to large file uploads in the file manager.
- **Session Handling**: Fixed session timeout issues in the web interface.

### Known Issues
- Some legacy features from the original Kloxo may not work as expected.
- Compatibility with third-party plugins or tools is not guaranteed.
- Performance optimizations are ongoing for high-traffic environments.
