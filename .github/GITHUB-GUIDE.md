# 📘 GitHub Complete Guide

Complete guide for using GitHub for Medical Booking System development.

## 🎯 Quick Start

### View Repository
**URL:** https://github.com/defaultroot-ai/cabinete

**Quick Links:**
- [Code](https://github.com/defaultroot-ai/cabinete)
- [Issues](https://github.com/defaultroot-ai/cabinete/issues)
- [Pull Requests](https://github.com/defaultroot-ai/cabinete/pulls)
- [Projects](https://github.com/defaultroot-ai/cabinete/projects)
- [Actions](https://github.com/defaultroot-ai/cabinete/actions)

---

## 📋 Creating Issues

### 1. Bug Report

**When to use:** Something is broken or not working as expected

**Steps:**
1. Go to [Issues](https://github.com/defaultroot-ai/cabinete/issues)
2. Click "New Issue"
3. Choose "Bug Report"
4. Fill in template:
   - Clear description
   - Steps to reproduce
   - Expected vs actual behavior
   - Environment (WordPress, PHP, browser versions)
   - Screenshots if possible
5. Add labels (will be added by maintainer)
6. Submit

**Example:**
```
Title: [BUG] CNP validation fails for valid CNP starting with 5

Description:
When registering with a valid CNP starting with 5 (for women born 2000+),
the validation fails even though the CNP is valid.

Steps to reproduce:
1. Go to /autentificare
2. Click "Înregistrare"
3. Enter CNP: 5010203040506
4. Fill other fields
5. Click submit
6. See error "CNP invalid"

Expected: Registration should succeed
Actual: Error message "CNP invalid"

Environment:
- WordPress: 6.4
- PHP: 8.1
- Browser: Chrome 120
- Plugin: v1.1.0
```

### 2. Feature Request

**When to use:** Suggest a new feature or enhancement

**Steps:**
1. Go to [Issues](https://github.com/defaultroot-ai/cabinete/issues)
2. Click "New Issue"
3. Choose "Feature Request"
4. Fill in template:
   - Clear feature description
   - Problem it solves
   - Proposed solution
   - Use cases
   - Priority level
5. Submit

**Example:**
```
Title: [FEATURE] Add SMS notifications for appointments

Description:
Send SMS notifications to patients 24h before appointment

Problem/Motivation:
Patients often forget appointments. Email notifications can be missed.
SMS has higher open rate.

Proposed Solution:
Integrate with Twilio API to send SMS notifications

Priority: High
User Impact: All patients
```

---

## 🔧 Working with Issues

### Assigning Yourself
```
1. Open issue
2. Right sidebar → Assignees
3. Select yourself
4. Issue moves to "In Progress" (if in project)
```

### Adding Labels
**Maintainers only** - Labels are added during triage

### Linking to Pull Requests
In PR description, add:
```
Fixes #123
Closes #456
Related to #789
```

### Closing Issues
Issues auto-close when PR is merged with `Fixes #123`

---

## 🔄 Pull Requests

### Creating a PR

**1. Create Branch**
```bash
git checkout -b feature/your-feature-name
```

**2. Make Changes**
```bash
# Make your changes
git add .
git commit -m "feat: your feature description"
```

**3. Push Branch**
```bash
git push origin feature/your-feature-name
```

**4. Create PR on GitHub**
1. Go to repository
2. Click "Pull Requests"
3. Click "New Pull Request"
4. Select your branch → main
5. Fill in PR template
6. Submit

### PR Checklist
- [ ] Descriptive title and description
- [ ] Links to related issues
- [ ] Code follows WordPress standards
- [ ] All tests pass
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] Screenshots added (if UI changes)

### PR Review Process
1. **Submitted** → Automated checks run (GitHub Actions)
2. **In Review** → Maintainer reviews code
3. **Changes Requested** → Author makes updates
4. **Approved** → Ready for merge
5. **Merged** → PR closed, issue(s) closed

---

## 📊 Using Projects

### View Projects
[Projects Tab](https://github.com/defaultroot-ai/cabinete/projects)

### Main Development Board

**Columns:**
- **📋 Backlog** - Ideas, low priority
- **🔍 To Triage** - New, needs review
- **✅ Ready** - Approved, ready to work
- **🚀 In Progress** - Being worked on
- **👀 In Review** - PR submitted
- **🧪 Testing** - Needs testing
- **✔️ Done** - Completed

**Usage:**
1. New issues auto-added to "To Triage"
2. After review, moved to "Ready"
3. When assigned, moved to "In Progress"
4. PR created → "In Review"
5. PR merged → "Done"

### Tracking Your Work
1. Go to Projects
2. Filter by your name
3. See all assigned tasks
4. Update status as you progress

---

## 🏷️ Labels System

### Label Categories

**Priority:**
- `priority: critical` 🔴 - Urgent, blocks functionality
- `priority: high` 🟠 - Important, should address soon
- `priority: medium` 🟡 - Normal priority
- `priority: low` ⚪ - Nice to have

**Component:**
- `component: auth` - Authentication
- `component: booking` - Booking system
- `component: admin` - Admin interface
- `component: api` - REST API
- `component: frontend` - React/UI
- `component: database` - Database
- `component: 2fa` - Two-factor auth

**Status:**
- `status: needs-triage` - Needs review
- `status: confirmed` - Confirmed bug/approved feature
- `status: in-progress` - Being worked on
- `status: blocked` - Blocked by another issue
- `status: needs-review` - Needs code review

**Type:**
- `bug` - Something broken
- `enhancement` - New feature
- `documentation` - Docs improvement
- `security` - Security issue
- `performance` - Performance improvement

**Special:**
- `good-first-issue` - Good for beginners
- `help-wanted` - Extra attention needed

---

## 🎯 Milestones

### Current Milestones

**v1.1.0** ✅ - Released (Oct 20, 2025)
- Authentication system
- Multi-phone support
- Documentation

**v1.2.0** 🚧 - In Progress (Target: Nov 2025)
- Admin UI improvements
- 2FA implementation
- Email notifications

**v1.3.0** 📋 - Planned (Target: Dec 2025)
- Patient dashboard
- SMS notifications
- Rating system

**v2.0.0** 💡 - Future (Target: Q1 2026)
- Multi-location
- Video consultations
- Payment integration

### Viewing Milestones
[Milestones Page](https://github.com/defaultroot-ai/cabinete/milestones)

---

## 🤖 GitHub Actions (CI/CD)

### Automated Workflows

**WordPress Plugin Check**
- **Triggers:** Push to main, PRs to main
- **Runs:**
  1. PHP syntax check
  2. Multi-version testing (PHP 7.4, 8.0, 8.1)
  3. WordPress compatibility (5.9, 6.0, 6.4)

**Status Badges:**
View in README.md

**Viewing Results:**
1. Go to [Actions tab](https://github.com/defaultroot-ai/cabinete/actions)
2. Click on workflow run
3. See results for each job

---

## 🔒 Security

### Reporting Security Issues

**DO NOT** create public issues for security vulnerabilities!

**Instead:**
1. Go to [Security tab](https://github.com/defaultroot-ai/cabinete/security)
2. Click "Report a vulnerability"
3. Fill in private security advisory
4. We'll respond within 48 hours

**Or email:** [security contact]

---

## 📚 Documentation

### Available Documentation

**In Repository:**
- [README.md](../README.md) - Main overview
- [CHANGELOG.md](../CHANGELOG.md) - Version history
- [CONTRIBUTING.md](../CONTRIBUTING.md) - How to contribute
- [STRUCTURE.md](../STRUCTURE.md) - File structure
- [docs/](../docs/) - Complete documentation

**GitHub Specific:**
- [GITHUB-SYNC.md](../GITHUB-SYNC.md) - Git workflow
- [LABELS.md](LABELS.md) - Labels guide
- [PROJECTS.md](PROJECTS.md) - Projects guide
- This file - Complete GitHub guide

---

## 💬 Communication

### Where to Ask Questions

**For:**
- **General questions** → [Discussions](https://github.com/defaultroot-ai/cabinete/discussions)
- **Bug reports** → [Issues](https://github.com/defaultroot-ai/cabinete/issues)
- **Feature requests** → [Issues](https://github.com/defaultroot-ai/cabinete/issues)
- **Security concerns** → Private security advisory
- **PR review** → PR comments

### Best Practices
- Be respectful and professional
- Provide context and details
- Search before creating new issue
- Follow templates
- Use code blocks for code
- Add screenshots when helpful

---

## 🎓 Learning Resources

### Git & GitHub
- [GitHub Docs](https://docs.github.com/)
- [Git Handbook](https://guides.github.com/introduction/git-handbook/)
- [GitHub Flow](https://guides.github.com/introduction/flow/)

### WordPress Development
- [Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Coding Standards](https://developer.wordpress.org/coding-standards/)

### Our Docs
- [Authentication Guide](../docs/AUTHENTICATION.md)
- [Quick Start](../docs/QUICK-START.md)
- [2FA Implementation](../docs/2FA-IMPLEMENTATION.md)
- [Project Plan](../docs/PROJECT-PLAN.md)

---

## ✅ Quick Reference

### Common Commands
```bash
# Clone
git clone https://github.com/defaultroot-ai/cabinete.git

# Update
git pull origin main

# Create branch
git checkout -b feature/name

# Commit
git add .
git commit -m "feat: description"

# Push
git push origin feature/name

# View status
git status

# View log
git log --oneline
```

### Common URLs
- Repository: https://github.com/defaultroot-ai/cabinete
- Issues: https://github.com/defaultroot-ai/cabinete/issues
- PRs: https://github.com/defaultroot-ai/cabinete/pulls
- Projects: https://github.com/defaultroot-ai/cabinete/projects
- Actions: https://github.com/defaultroot-ai/cabinete/actions
- Releases: https://github.com/defaultroot-ai/cabinete/releases

---

## 🆘 Getting Help

**Stuck?** Try these steps:
1. Check [documentation](../docs/)
2. Search [existing issues](https://github.com/defaultroot-ai/cabinete/issues)
3. Ask in [discussions](https://github.com/defaultroot-ai/cabinete/discussions)
4. Create new issue with detailed info

**For urgent matters:**
Contact maintainers directly (see CONTRIBUTING.md)

---

*Last updated: October 20, 2025*  
*Repository: https://github.com/defaultroot-ai/cabinete*  
*Status: ✅ Active Development*

