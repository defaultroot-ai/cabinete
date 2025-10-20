# 🔄 GitHub Synchronization Guide

## 📍 Repository Information

**GitHub Repository:** https://github.com/defaultroot-ai/cabinete.git  
**Branch:** main  
**Status:** ✅ Synchronized  
**Last sync:** October 20, 2025

---

## ✅ What's Synced

### Initial Commit (d28ed73)
**Files:** 31 files, 7,191 lines  
**Message:** "Initial commit: Medical Booking System v1.1.0"

**Includes:**
- ✅ Complete plugin structure
- ✅ Authentication system (CNP, multi-phone)
- ✅ REST API endpoints
- ✅ React components (booking, auth)
- ✅ Admin interface
- ✅ Database schema
- ✅ Complete documentation (6 docs)
- ✅ Romanian translations
- ✅ README, CHANGELOG, LICENSE

### Second Commit (45a9f0a)
**Files:** 2 files, 236 lines  
**Message:** "chore: add GitHub Actions workflow and CONTRIBUTING.md"

**Includes:**
- ✅ GitHub Actions CI/CD workflow
- ✅ Contributing guidelines

---

## 📦 Repository Structure

```
defaultroot-ai/cabinete (main branch)
├── .github/workflows/
│   └── wordpress-plugin-check.yml    ← CI/CD
├── admin/                             ← Admin UI
├── assets/                            ← JS, CSS, images
├── docs/                              ← Documentation (6 files)
├── examples/                          ← Code examples
├── includes/                          ← Core classes
├── languages/                         ← Translations
├── public/                            ← Frontend
├── tests/                             ← Unit tests
├── vendor/                            ← Third-party libs
├── .gitignore                         ← Git ignore rules
├── CHANGELOG.md                       ← Version history
├── CONTRIBUTING.md                    ← Contribution guide
├── LICENSE.txt                        ← GPL v2
├── ORGANIZATION-SUMMARY.md            ← Reorganization details
├── README.md                          ← Main documentation
├── STRUCTURE.md                       ← File structure
└── medical-booking-system.php         ← Main plugin file
```

---

## 🔄 Git Workflow

### Daily Development Workflow

1. **Pull latest changes**
   ```bash
   cd C:\xampp8.2.12\htdocs\react\wp-content\plugins\medical-booking-system
   git pull origin main
   ```

2. **Create feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make changes and commit**
   ```bash
   git add .
   git commit -m "feat: your feature description"
   ```

4. **Push to GitHub**
   ```bash
   git push origin feature/your-feature-name
   ```

5. **Create Pull Request on GitHub**
   - Go to https://github.com/defaultroot-ai/cabinete
   - Click "Pull Requests" → "New Pull Request"
   - Select your branch → main
   - Fill in details and submit

### Quick Sync (for main branch)

```bash
# Add changes
git add .

# Commit
git commit -m "your commit message"

# Push
git push origin main
```

---

## 📝 Commit Message Convention

We use [Conventional Commits](https://www.conventionalcommits.org/):

**Format:**
```
<type>(<scope>): <subject>
```

**Types:**
- `feat:` - New feature (e.g., "feat(auth): add 2FA support")
- `fix:` - Bug fix (e.g., "fix(booking): resolve double-booking")
- `docs:` - Documentation (e.g., "docs: update API reference")
- `style:` - Code formatting (e.g., "style: fix indentation")
- `refactor:` - Code refactoring (e.g., "refactor(api): simplify auth logic")
- `test:` - Tests (e.g., "test: add CNP validation tests")
- `chore:` - Maintenance (e.g., "chore: update dependencies")

**Examples:**
```bash
git commit -m "feat(2fa): implement TOTP authentication"
git commit -m "fix(booking): prevent past date selection"
git commit -m "docs(auth): add CNP validation examples"
git commit -m "refactor(database): optimize queries"
```

---

## 🌿 Branch Strategy

### Main Branches
- **main** - Production-ready code, always stable
- **develop** (future) - Development integration branch

### Supporting Branches
- **feature/*** - New features (e.g., `feature/2fa-totp`)
- **bugfix/*** - Bug fixes (e.g., `bugfix/booking-validation`)
- **hotfix/*** - Critical production fixes (e.g., `hotfix/security-patch`)
- **docs/*** - Documentation updates (e.g., `docs/api-reference`)

### Branch Naming Examples
```
feature/2fa-implementation
feature/email-notifications
bugfix/appointment-timezone
hotfix/cnp-validation-security
docs/quick-start-guide
```

---

## 🔒 .gitignore

**Currently ignored:**
- Node modules
- Vendor directory (until needed)
- Compiled .mo files
- Environment files (.env)
- IDE files (.vscode, .idea)
- OS files (.DS_Store, Thumbs.db)
- Build artifacts
- Database backups
- Log files

**Not ignored (intentionally tracked):**
- Source .po translation files
- Documentation
- All PHP source files
- All JS source files
- Configuration files

---

## 🚀 GitHub Actions

**Workflow:** `.github/workflows/wordpress-plugin-check.yml`

**Triggers:**
- Push to `main` or `develop`
- Pull requests to `main`

**Jobs:**
1. **Lint** - PHP syntax check
2. **Test** - Multi-version testing
   - PHP: 7.4, 8.0, 8.1
   - WordPress: 5.9, 6.0, 6.4

**Status:** Will run automatically on next push

---

## 📊 Repository Statistics

| Metric | Value |
|--------|-------|
| **Total Files** | 33 |
| **Total Lines** | ~7,400 |
| **PHP Files** | 15 |
| **JavaScript Files** | 3 |
| **Documentation** | 9 files |
| **Commits** | 2 |
| **Contributors** | 1 |
| **Size** | ~500 KB |

---

## 🔗 Useful Commands

### Check Status
```bash
git status
git log --oneline -10
git branch -a
```

### View Changes
```bash
git diff
git diff --staged
git show HEAD
```

### Undo Changes
```bash
git restore <file>              # Discard local changes
git restore --staged <file>     # Unstage file
git reset --soft HEAD~1         # Undo last commit (keep changes)
git reset --hard HEAD~1         # Undo last commit (discard changes)
```

### Sync with Remote
```bash
git fetch origin                # Download changes
git pull origin main            # Merge changes
git push origin main            # Upload changes
```

### Branch Management
```bash
git branch                      # List local branches
git branch -a                   # List all branches
git checkout -b feature/name    # Create and switch to branch
git branch -d feature/name      # Delete local branch
git push origin --delete feature/name  # Delete remote branch
```

---

## 🆘 Common Issues

### Issue: Permission Denied
```bash
# Solution: Use HTTPS with personal access token
git remote set-url origin https://github.com/defaultroot-ai/cabinete.git
```

### Issue: Merge Conflicts
```bash
# 1. Pull latest changes
git pull origin main

# 2. Resolve conflicts in files
# Edit files marked with <<<<<<<, =======, >>>>>>>

# 3. Mark as resolved
git add .

# 4. Complete merge
git commit
```

### Issue: Pushed Wrong Commit
```bash
# Revert last commit (creates new commit)
git revert HEAD
git push origin main
```

---

## 📱 Mobile Development

**Using GitHub Mobile App:**
1. Download GitHub app (iOS/Android)
2. Sign in to defaultroot-ai account
3. View repository: cabinete
4. Review PRs, issues, commits on the go

**Using Git on Phone:**
- Termux (Android) - Git client
- Working Copy (iOS) - Git client

---

## 🎯 Next Steps

### Immediate
- [ ] Clone on other machines
- [ ] Set up develop branch
- [ ] Create first feature branch
- [ ] Test CI/CD pipeline

### Short-term
- [ ] Add code owners (CODEOWNERS file)
- [ ] Set up branch protection rules
- [ ] Configure issue templates
- [ ] Add PR templates
- [ ] Set up project boards

### Long-term
- [ ] Automated testing (PHPUnit)
- [ ] Code coverage reports
- [ ] Automated releases
- [ ] WordPress.org deployment pipeline

---

## 📚 Resources

**Git Documentation:**
- [Git Basics](https://git-scm.com/book/en/v2/Getting-Started-Git-Basics)
- [GitHub Guides](https://guides.github.com/)
- [Conventional Commits](https://www.conventionalcommits.org/)

**WordPress Plugin Development:**
- [Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Plugin SVN to Git](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/)

**Repository:**
- [GitHub Repository](https://github.com/defaultroot-ai/cabinete)
- [Issues](https://github.com/defaultroot-ai/cabinete/issues)
- [Pull Requests](https://github.com/defaultroot-ai/cabinete/pulls)

---

## ✅ Sync Verification

To verify sync is working:

```bash
# 1. Check remote
git remote -v

# Should show:
# origin  https://github.com/defaultroot-ai/cabinete.git (fetch)
# origin  https://github.com/defaultroot-ai/cabinete.git (push)

# 2. Check status
git status

# Should show:
# On branch main
# Your branch is up to date with 'origin/main'.

# 3. Test push
echo "test" >> test.txt
git add test.txt
git commit -m "test: verify sync"
git push origin main
git rm test.txt
git commit -m "test: cleanup"
git push origin main
```

---

**Last Updated:** October 20, 2025  
**Repository:** https://github.com/defaultroot-ai/cabinete  
**Status:** ✅ **FULLY SYNCHRONIZED**

