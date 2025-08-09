# A11y Nightly Workflow Production Guide

## 📋 Overview

The `a11y-nightly.yml` workflow provides automated accessibility testing for the Monthly Booking plugin using Playwright and axe-core. This guide covers production deployment, troubleshooting, and monitoring.

## 🚨 API Permissions Issue

### Problem
`gh workflow run` fails with **HTTP 403: Resource not accessible by integration**

### Root Cause
GitHub API integration lacks workflow dispatch permissions for the devin-ai-integration bot.

### Workarounds
1. **GitHub UI Manual Trigger**: Actions → a11y-nightly → Run workflow
2. **Repository Settings**: Settings → Actions → Workflow permissions → Read and write permissions
3. **Alternative**: Use scheduled execution (daily 1 AM UTC / 10 PM JST)

### Recommended Solution
Update repository workflow permissions:
```
Settings → Actions → General → Workflow permissions
✅ Read and write permissions
✅ Allow GitHub Actions to create and approve pull requests
```

## 🔄 Workflow Execution Methods

### 1. Scheduled Execution (Recommended)
- **Schedule**: Daily at 1 AM UTC (10 PM JST)
- **Automatic**: No manual intervention required
- **Monitoring**: Check Actions tab for daily runs

### 2. Manual Trigger via GitHub UI
1. Navigate to **Actions** tab
2. Select **a11y-nightly** workflow
3. Click **Run workflow** button
4. Select branch (usually `main`)
5. Click **Run workflow**

### 3. Manual Trigger via CLI (Limited)
```bash
# May fail with HTTP 403
gh workflow run a11y-nightly.yml --ref main
```

## 📊 Workflow Execution Verification

### Monitoring Checklist
- [ ] **Execution Time**: Target < 10 minutes
- [ ] **WordPress Setup**: wp-env starts successfully
- [ ] **Plugin Activation**: monthly-booking activates without errors
- [ ] **Health Checks**: Calendar page responds with HTTP 200
- [ ] **Axe Tests**: @axe tagged tests execute
- [ ] **Artifact Generation**: playwright-report and test-results created

### Artifact Access
1. Go to **Actions** → Select workflow run
2. Scroll to **Artifacts** section
3. Download **playwright-report** and **test-results**
4. Extract and open `index.html` for detailed results

## 🎯 KPI Monitoring

### Performance Metrics
- **Target Execution Time**: < 10 minutes
- **WordPress Startup**: < 2 minutes
- **Test Execution**: < 1 minute
- **Artifact Size**: < 50MB total

### Quality Metrics
- **Critical Violations**: 0 expected
- **Serious Violations**: 0 expected
- **Moderate Violations**: < 5 acceptable
- **Minor Violations**: < 10 acceptable

### Success Indicators
- ✅ All @axe tests pass
- ✅ No critical/serious violations
- ✅ Artifacts generated successfully
- ✅ No workflow timeouts

## 🚨 Issue Auto-Creation

### Trigger Conditions
- Critical accessibility violations detected
- Serious accessibility violations detected
- Workflow fails due to axe violations

### Issue Format
```
Title: [a11y] Critical accessibility violations detected
Labels: accessibility, bug
Body: Automated accessibility scan found critical/serious violations: [details]
```

### Response Process
1. **Immediate**: Review workflow artifacts
2. **Analysis**: Identify violation root causes
3. **Fix**: Implement accessibility corrections
4. **Verify**: Re-run workflow to confirm resolution
5. **Close**: Close issue when violations resolved

## 🔧 Troubleshooting

### Common Issues

#### 1. Workflow Fails to Start
**Symptoms**: No workflow run appears in Actions
**Causes**: 
- Workflow file syntax errors
- Branch protection rules
- Insufficient permissions

**Solutions**:
- Validate YAML syntax
- Check branch protection settings
- Verify workflow permissions

#### 2. WordPress Setup Fails
**Symptoms**: wp-env start errors
**Causes**:
- Docker issues
- Port conflicts
- Resource limitations

**Solutions**:
- Check Docker daemon status
- Verify port availability
- Increase runner resources

#### 3. Axe Tests Fail
**Symptoms**: Critical/serious violations detected
**Causes**:
- Missing ARIA attributes
- Color contrast issues
- Keyboard navigation problems

**Solutions**:
- Review violation details in artifacts
- Fix accessibility issues in code
- Re-run tests to verify fixes

#### 4. Artifact Upload Fails
**Symptoms**: No artifacts available
**Causes**:
- Disk space issues
- Permission problems
- Workflow interruption

**Solutions**:
- Check runner disk space
- Verify artifact upload permissions
- Review workflow logs for errors

## 📅 Schedule Optimization

### Current Schedule
- **UTC**: 1:00 AM daily
- **JST**: 10:00 PM daily
- **Rationale**: Avoids Japanese business hours and GitHub Actions peak usage

### Alternative Schedules
```yaml
# Every 6 hours
- cron: '0 */6 * * *'

# Weekdays only at 1 AM UTC
- cron: '0 1 * * 1-5'

# Multiple times per day
- cron: '0 1,13 * * *'
```

## 🔒 Security Considerations

### Secrets Management
- **GITHUB_TOKEN**: Automatically provided
- **No additional secrets**: Required for basic operation
- **Custom secrets**: Add via Settings → Secrets if needed

### Permissions
- **Contents**: read (for code checkout)
- **Actions**: read (for workflow execution)
- **Issues**: write (for auto-creation)
- **Pull-requests**: write (if needed for fixes)

## 📈 Continuous Improvement

### Monitoring Recommendations
1. **Weekly Review**: Check violation trends
2. **Monthly Analysis**: Review execution time patterns
3. **Quarterly Optimization**: Update axe rules and thresholds
4. **Annual Review**: Evaluate workflow effectiveness

### Enhancement Opportunities
1. **Parallel Testing**: Multiple browser testing
2. **Custom Rules**: Project-specific accessibility rules
3. **Integration**: Slack/email notifications
4. **Reporting**: Dashboard for accessibility metrics

## 📞 Support Contacts

### Technical Issues
- **Repository**: https://github.com/yoshaaa888/monthly-booking
- **Workflow File**: `.github/workflows/a11y-nightly.yml`
- **Test Files**: `tests/a11y/calendar.a11y.spec.ts`

### Documentation
- **Playwright**: https://playwright.dev/docs/accessibility-testing
- **Axe-core**: https://github.com/dequelabs/axe-core
- **GitHub Actions**: https://docs.github.com/en/actions

---

**Last Updated**: 2025-08-09  
**Version**: 1.0  
**Maintainer**: Devin AI Integration
