# Code Coverage Setup Guide

This guide explains how to set up code coverage reporting for pull requests using Codecov.

## 🎯 Overview

The repository is configured to:
- Generate code coverage reports on every test run
- Upload coverage data to Codecov for PR analysis
- Display coverage badges and PR comments
- Maintain coverage targets (80% minimum)

## 🔧 Setup Steps

### 1. GitHub Repository Secrets

You need to add the Codecov token as a GitHub secret:

1. Go to your GitHub repository
2. Navigate to **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**
4. Name: `CODECOV_TOKEN`
5. Value: `41307ad9-2594-4211-acb5-cfb401572fd5` (already configured in codecov.yml)

### 2. Codecov Account Setup

1. Visit [codecov.io](https://codecov.io)
2. Sign in with your GitHub account
3. Add your repository to Codecov
4. The token in `codecov.yml` should match your repository token

## 📊 Coverage Reports

### Local Development

Generate coverage reports locally:

```bash
# Using Composer
composer test:coverage

# Using Make (Docker)
make test-coverage

# View coverage report
composer test:coverage-report
make test-coverage-report
```

### Coverage Files Generated

- `coverage.xml` - Clover format for Codecov
- `coverage.txt` - Text summary
- `coverage-html/` - HTML report (open `coverage-html/index.html` in browser)

## 🎯 Coverage Targets

Current configuration:
- **Project coverage**: 80% minimum
- **Patch coverage**: 80% minimum (new code in PRs)
- **Threshold**: 1% (allowed decrease)

## 📝 PR Comments

Codecov will automatically:
- Comment on PRs with coverage analysis
- Show coverage diff for changed files
- Display coverage tree for the entire project
- Highlight uncovered lines

## 🚫 Ignored Files

The following directories are excluded from coverage:
- `bootstrap/`
- `config/`
- `database/migrations/`
- `database/seeders/`
- `public/`
- `resources/`
- `routes/`
- `storage/`
- `vendor/`
- `tests/`

## 🔍 Viewing Coverage

### GitHub Actions
- Coverage reports are uploaded as artifacts
- Available for 30 days after test runs
- Download from the Actions tab

### Codecov Dashboard
- Visit your repository on codecov.io
- View detailed coverage reports
- Track coverage trends over time
- Analyze coverage by file/directory

## 🛠️ Troubleshooting

### Coverage Not Uploading
1. Check GitHub Actions logs
2. Verify `CODECOV_TOKEN` secret is set
3. Ensure tests are generating `coverage.xml`

### Low Coverage Warnings
1. Add more tests for uncovered code
2. Review ignored files in `codecov.yml`
3. Adjust coverage targets if needed

### PR Comments Not Appearing
1. Verify Codecov GitHub app permissions
2. Check repository settings on codecov.io
3. Ensure PR is from a branch (not fork)

## 📈 Best Practices

1. **Write tests first** - Aim for high coverage from the start
2. **Focus on critical paths** - Prioritize business logic coverage
3. **Review coverage reports** - Use them to identify gaps
4. **Don't chase 100%** - Focus on meaningful tests over coverage percentage
5. **Monitor trends** - Watch for coverage decreases over time

## 🔗 Useful Links

- [Codecov Documentation](https://docs.codecov.io/)
- [PHPUnit Coverage](https://phpunit.readthedocs.io/en/9.5/code-coverage-analysis.html)
- [Laravel Testing](https://laravel.com/docs/testing)
