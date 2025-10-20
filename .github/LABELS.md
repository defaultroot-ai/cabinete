# GitHub Labels Configuration

## 🏷️ Label Categories

### Priority
| Label | Color | Description |
|-------|-------|-------------|
| `priority: critical` | #D73A4A | Critical issue that blocks functionality |
| `priority: high` | #FF6B6B | High priority, should be addressed soon |
| `priority: medium` | #FFA500 | Medium priority |
| `priority: low` | #FFEB3B | Low priority, nice to have |

### Type
| Label | Color | Description |
|-------|-------|-------------|
| `bug` | #D73A4A | Something isn't working |
| `enhancement` | #A2EEEF | New feature or request |
| `documentation` | #0075CA | Documentation improvements |
| `security` | #FF0000 | Security-related issue |
| `performance` | #FF9800 | Performance improvement |
| `refactor` | #9C27B0 | Code refactoring |
| `test` | #4CAF50 | Testing related |

### Component
| Label | Color | Description |
|-------|-------|-------------|
| `component: auth` | #1E88E5 | Authentication system |
| `component: booking` | #43A047 | Booking system |
| `component: admin` | #FDD835 | Admin interface |
| `component: api` | #00897B | REST API |
| `component: frontend` | #5E35B1 | Frontend/React |
| `component: database` | #E53935 | Database related |
| `component: 2fa` | #1976D2 | Two-factor authentication |

### Status
| Label | Color | Description |
|-------|-------|-------------|
| `status: needs-triage` | #FBCA04 | Needs initial review |
| `status: confirmed` | #0E8A16 | Bug confirmed or feature approved |
| `status: in-progress` | #1D76DB | Currently being worked on |
| `status: blocked` | #B60205 | Blocked by another issue |
| `status: needs-review` | #6F42C1 | Needs code review |
| `status: needs-testing` | #FF9800 | Needs testing |

### Effort
| Label | Color | Description |
|-------|-------|-------------|
| `effort: 1-hour` | #C5DEF5 | Quick fix, ~1 hour |
| `effort: 1-day` | #BFD4F2 | Small task, ~1 day |
| `effort: 1-week` | #7CB342 | Medium task, ~1 week |
| `effort: 1-month` | #FBC02D | Large task, ~1 month |

### Special
| Label | Color | Description |
|-------|-------|-------------|
| `good-first-issue` | #7057FF | Good for newcomers |
| `help-wanted` | #008672 | Extra attention is needed |
| `duplicate` | #CFD3D7 | This issue already exists |
| `wontfix` | #FFFFFF | This will not be worked on |
| `question` | #D876E3 | Further information is requested |

## 📋 How to Apply Labels

### On GitHub Web
1. Go to Issues or Pull Requests
2. Click on the issue/PR
3. Click "Labels" in the right sidebar
4. Select appropriate labels

### Using GitHub CLI
```bash
# Add label to issue
gh issue edit 123 --add-label "bug,priority: high"

# Remove label
gh issue edit 123 --remove-label "priority: low"
```

## 🎯 Label Guidelines

### For Bug Reports
Always add:
- `bug`
- Priority label
- Component label(s)
- Effort estimate (after triage)

Example: `bug`, `priority: high`, `component: auth`, `effort: 1-day`

### For Feature Requests
Always add:
- `enhancement`
- Priority label
- Component label(s)
- Effort estimate (after approval)

Example: `enhancement`, `priority: medium`, `component: 2fa`, `effort: 1-week`

### For Pull Requests
Add based on changes:
- Type label (bug fix, enhancement, etc.)
- Component label(s)
- `status: needs-review`

## 🔄 Label Workflow

### Issue Lifecycle
```
New Issue → needs-triage
  ↓
confirmed → in-progress
  ↓
needs-review → needs-testing
  ↓
Closed
```

### PR Lifecycle
```
New PR → needs-review
  ↓
approved → needs-testing
  ↓
Merged & Closed
```

## 📊 Label Statistics

Track label usage to understand:
- Most common bug types
- Most requested features
- Component that needs most attention
- Effort distribution

## 🛠️ Creating Labels via GitHub CLI

```bash
# Create all labels at once
gh label create "priority: critical" --color D73A4A --description "Critical issue"
gh label create "priority: high" --color FF6B6B --description "High priority"
gh label create "priority: medium" --color FFA500 --description "Medium priority"
gh label create "priority: low" --color FFEB3B --description "Low priority"

gh label create "component: auth" --color 1E88E5 --description "Authentication system"
gh label create "component: booking" --color 43A047 --description "Booking system"
gh label create "component: admin" --color FDD835 --description "Admin interface"
gh label create "component: api" --color 00897B --description "REST API"
gh label create "component: frontend" --color 5E35B1 --description "Frontend/React"
gh label create "component: database" --color E53935 --description "Database"
gh label create "component: 2fa" --color 1976D2 --description "Two-factor auth"

gh label create "status: needs-triage" --color FBCA04 --description "Needs review"
gh label create "status: confirmed" --color 0E8A16 --description "Confirmed"
gh label create "status: in-progress" --color 1D76DB --description "In progress"
gh label create "status: blocked" --color B60205 --description "Blocked"
gh label create "status: needs-review" --color 6F42C1 --description "Needs review"
gh label create "status: needs-testing" --color FF9800 --description "Needs testing"

gh label create "effort: 1-hour" --color C5DEF5 --description "~1 hour"
gh label create "effort: 1-day" --color BFD4F2 --description "~1 day"
gh label create "effort: 1-week" --color 7CB342 --description "~1 week"
gh label create "effort: 1-month" --color FBC02D --description "~1 month"
```

## 📖 References

- [GitHub Labels Documentation](https://docs.github.com/en/issues/using-labels-and-milestones-to-track-work/managing-labels)
- [Label Best Practices](https://github.com/dotnet/corefx/blob/master/Documentation/contributing/labels.md)

