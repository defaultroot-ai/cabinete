# Contributing to Medical Booking System

Thank you for your interest in contributing to the Medical Booking System plugin!

## 📋 Development Workflow

### Setting Up Development Environment

1. **Clone the repository**
   ```bash
   git clone https://github.com/defaultroot-ai/cabinete.git
   cd cabinete
   ```

2. **Install in WordPress**
   ```bash
   # Copy to your WordPress installation
   cp -r . /path/to/wordpress/wp-content/plugins/medical-booking-system/
   ```

3. **Activate plugin**
   - Go to WordPress Admin → Plugins
   - Activate "Medical Booking System"

### Branch Strategy

- `main` - Production-ready code
- `develop` - Development branch
- `feature/*` - New features
- `bugfix/*` - Bug fixes
- `hotfix/*` - Critical fixes

### Making Changes

1. **Create a branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**
   - Follow WordPress Coding Standards
   - Add/update documentation
   - Test thoroughly

3. **Commit your changes**
   ```bash
   git add .
   git commit -m "feat: your feature description"
   ```

4. **Push to GitHub**
   ```bash
   git push origin feature/your-feature-name
   ```

5. **Create a Pull Request**
   - Go to GitHub
   - Create PR from your branch to `develop`
   - Fill in the PR template

## 📝 Commit Message Format

We use [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting)
- `refactor:` - Code refactoring
- `test:` - Adding tests
- `chore:` - Maintenance tasks

**Examples:**
```
feat(auth): add 2FA support with Google Authenticator
fix(booking): resolve double-booking issue
docs(api): update REST API endpoints documentation
```

## 🧪 Testing

### Manual Testing
1. Test all affected features
2. Test on different PHP versions (7.4, 8.0, 8.1)
3. Test on different WordPress versions (5.9+)
4. Test in different browsers

### Automated Tests (Coming Soon)
```bash
# Run PHPUnit tests
composer test

# Run PHPCS
composer phpcs
```

## 📚 Documentation

When adding new features:
- Update `README.md` if needed
- Add to `docs/` folder if substantial
- Update `CHANGELOG.md`
- Add inline comments for complex code
- Update API documentation

## 🔒 Security

**Reporting Security Issues:**
- DO NOT open public issues for security vulnerabilities
- Email security concerns to: [your-email]
- We will respond within 48 hours

## 💻 Code Standards

### PHP
- Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- Use proper WordPress functions (e.g., `wp_safe_redirect()`, `esc_html()`)
- Always sanitize input, escape output
- Use prepared statements for database queries

### JavaScript
- Use ES6+ features
- Follow React best practices
- Use meaningful variable names
- Add comments for complex logic

### CSS
- Use BEM methodology when possible
- Ensure mobile responsiveness
- Test in multiple browsers

## 📁 File Structure

When adding new files:
- PHP classes → `includes/class-*.php`
- Admin UI → `admin/`
- Frontend → `public/`
- React components → `assets/js/components/`
- Styles → `assets/css/`
- Documentation → `docs/`

## ✅ Pull Request Checklist

Before submitting a PR, ensure:

- [ ] Code follows WordPress coding standards
- [ ] All functions are documented
- [ ] Changes are tested manually
- [ ] No console errors in browser
- [ ] Documentation is updated
- [ ] CHANGELOG.md is updated
- [ ] Commit messages follow convention
- [ ] Branch is up to date with develop

## 🎯 Priority Areas

We especially welcome contributions in:
- ✅ 2FA implementation (TOTP)
- ✅ Admin UI improvements
- ✅ Email notification system
- ✅ Unit tests
- ✅ Translation improvements
- ✅ Performance optimizations

See [docs/TODO.md](docs/TODO.md) for detailed roadmap.

## 📞 Getting Help

- Review [documentation](docs/README.md)
- Check [existing issues](https://github.com/defaultroot-ai/cabinete/issues)
- Ask questions in discussions

## 📄 License

By contributing, you agree that your contributions will be licensed under the GPL v2 or later license.

---

Thank you for contributing! 🎉

