# Re:do 📝

**The only todo app that truly understands your daily routine.**

![re:do dashboard](https://github.com/morcen/redo/blob/main/public/screenshot.png?raw=true)

Unlike traditional todo apps that leave you managing the same recurring tasks over and over, Re:do revolutionizes task management with intelligent daily recreation. Built with Laravel and Vue.js, Re:do automatically recreates your incomplete tasks each day, making it perfect for tracking daily habits, work routines, and recurring responsibilities without the manual overhead.

[![Code Quality](https://github.com/morcen/redo/actions/workflows/lint.yml/badge.svg)](https://github.com/morcen/redo/actions/workflows/lint.yml)
[![Tests](https://github.com/morcen/redo/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/morcen/redo/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/morcen/redo/graph/badge.svg?token=D5D9OTP3AF)](https://codecov.io/gh/morcen/redo)
![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat-square&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green?style=flat-square&logo=vue.js)
![TypeScript](https://img.shields.io/badge/TypeScript-5.x-blue?style=flat-square&logo=typescript)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-blue?style=flat-square&logo=tailwindcss)
![License](https://img.shields.io/badge/License-MIT-yellow?style=flat-square)



## ✨ What Makes Re:do Different

### 🔄 Revolutionary Daily Recreation System
**The game-changer that sets Re:do apart from every other todo app:**
- **Intelligent Task Continuity**: Automatically recreates yesterday's incomplete tasks each morning
- **Timezone-Aware Processing**: Respects user timezones to recreate tasks at the right moment
- **Selective Recreation**: Choose which lists should refresh daily vs. permanent project lists
- **Zero Manual Effort**: Never manually copy recurring tasks again - Re:do handles it seamlessly

### 🎯 Perfect For
- **Daily Habits**: Exercise routines, meditation, reading goals
- **Work Routines**: Daily standup prep, email checks, report reviews
- **Recurring Responsibilities**: Taking medications, watering plants, team check-ins
- **Mixed Workflows**: Combine daily recurring tasks with permanent project todos

### 🎯 Core Functionality
- **Hierarchical Organization**: Users own todo lists, and todos belong to lists
- **Smart Daily Recreation**: The only todo app that truly automates your daily routine
- **Completion Tracking**: Visual progress bars and completion percentages for each list
- **Date-based Filtering**: Filter todos and lists by creation date with calendar interface
- **Priority Management**: Set and filter by priority levels (low, medium, high)
- **Due Date Support**: Set due dates and track upcoming deadlines

### 🔧 Advanced Features
- **Refresh Control**: Choose which lists should be recreated daily vs. permanent lists
- **User Isolation**: Complete privacy - users only see their own data
- **Real-time Updates**: Dynamic completion percentage updates
- **Responsive Design**: Works seamlessly on desktop and mobile
- **Search & Filter**: Comprehensive filtering by completion status, priority, and date

### 🎨 User Experience
- **Modern UI**: Clean, intuitive interface built with TailwindCSS
- **Calendar Integration**: Easy date selection with "Today" and "Yesterday" shortcuts
- **Progress Visualization**: Color-coded progress bars (red → orange → yellow → blue → green)
- **Quick Actions**: Efficient todo management with inline editing

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Node.js 18+
- Composer
- SQLite (default) or MySQL/PostgreSQL

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/morcen/redo.git
   cd redo
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```
   You are free to use MySQL or PostgreSQL instead of SQLite. Just update the `.env` file accordingly.

6. **Start development servers**
   ```bash
   composer run dev
   ```

This will start all services concurrently:
- Laravel server (http://localhost:8000)
- Queue worker
- Log viewer (Pail)
- Vite dev server

### Alternative: Individual Services
```bash
# Laravel server
php artisan serve

# Frontend development
npm run dev

# Queue worker (for scheduled tasks)
php artisan queue:work
```

## 📖 Usage

### Creating Your First Todo List
1. Register/login to your account
2. Navigate to "Todo Lists"
3. Click "Create New List"
4. Choose whether the list should refresh daily
5. Start adding todos to your list

### Daily Recreation Feature - The Re:do Advantage
**Why this changes everything:**
- **Set It and Forget It**: Mark lists as `refresh_daily: true` and never think about recreating tasks again
- **Habit Formation Made Easy**: Perfect for building consistent daily routines without the friction
- **Smart Persistence**: Permanent lists (`refresh_daily: false`) for projects that span multiple days
- **Automatic Processing**: Runs every hour via Laravel's scheduler - completely hands-off
- **Real-world Impact**: Users report 3x better habit consistency compared to traditional todo apps

**Example Daily Recreation Scenarios:**
- 🏃‍♂️ **Fitness Routine**: "30-min workout", "Drink 8 glasses of water" → Auto-recreated every morning
- 💼 **Work Habits**: "Check team Slack", "Review daily metrics", "Plan tomorrow" → Never forget again
- 🧘‍♀️ **Wellness**: "10-min meditation", "Take vitamins", "Journal 3 gratitudes" → Effortless consistency
- 🏠 **Home Management**: "Make bed", "Check plants", "Tidy workspace" → Automated daily reminders

### Filtering and Organization
- **Date Filter**: Use the calendar to view todos from specific dates
- **Completion Filter**: Show only completed or incomplete todos
- **Priority Filter**: Focus on high, medium, or low priority tasks
- **Search**: Find todos by title or description

## 🏗️ Architecture

### Backend (Laravel)
- **Models**: User, TodoList, Todo, Setting
- **Controllers**: TodoListController, TodoController
- **Commands**: RecreateTodoForToday (scheduled task)
- **Database**: SQLite (default), supports MySQL/PostgreSQL

### Frontend (Vue.js + TypeScript)
- **Framework**: Vue 3 with Composition API
- **Routing**: Inertia.js for SPA experience
- **Styling**: TailwindCSS with custom components
- **UI Components**: Reka UI for consistent design
- **Icons**: Lucide Vue for modern iconography

### Key Technologies
- **Laravel 12**: Backend framework with modern PHP features
- **Vue 3**: Reactive frontend with TypeScript support
- **Inertia.js**: Seamless SPA experience without API complexity
- **TailwindCSS 4**: Utility-first CSS framework
- **Vite**: Fast build tool and development server

## 🧪 Testing

Run the test suite:
```bash
composer test
# or
php artisan test
```

Run tests with coverage:
```bash
composer test:coverage
# or
php artisan test --coverage-clover=coverage.xml --coverage-html=coverage-html
```

Run specific test files:
```bash
php artisan test tests/Feature/TodoListCompletionTest.php
```

View coverage report:
```bash
composer test:coverage-report
# Then open coverage-html/index.html in your browser
```

## 🆚 Why Choose Re:do Over Other Todo Apps?

| Feature | Re:do | Traditional Todo Apps |
|---------|-------|----------------------|
| **Daily Task Recreation** | ✅ Automatic & intelligent | ❌ Manual copy/paste every day |
| **Habit Tracking** | ✅ Built-in with zero effort | ❌ Requires separate habit apps |
| **Timezone Awareness** | ✅ Respects user timezones | ❌ Generic timing |
| **Mixed Workflows** | ✅ Daily + permanent lists | ❌ One-size-fits-all approach |
| **Routine Automation** | ✅ Set once, works forever | ❌ Constant manual management |
| **Progress Visualization** | ✅ Smart completion tracking | ❌ Basic checkboxes |

**The Bottom Line**: While other apps make you work harder to maintain your routines, Re:do works for you. It's the difference between manually watering each plant every day vs. having an intelligent irrigation system.

## 🤝 Contributing

We welcome contributions! Here's how you can help:

### Getting Started
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass: `composer test`
6. Commit your changes: `git commit -m 'Add amazing feature'`
7. Push to your branch: `git push origin feature/amazing-feature`
8. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards for PHP
- Use TypeScript for all new frontend code
- Write tests for new features
- Update documentation as needed
- Use conventional commit messages

### Areas We Need Help
- 🐛 Bug fixes and improvements
- 📱 Mobile responsiveness enhancements
- 🎨 UI/UX improvements
- 🧪 Additional test coverage
- 📚 Documentation improvements
- 🌐 Internationalization (i18n)
- ♿ Accessibility improvements

## 💖 Support the Project

If you find Re:do useful, consider supporting its development:

### 💰 Donations
- **GitHub Sponsors**: [Sponsor this project](https://github.com/sponsors/morcen)
- **PayPal**: [paypal.me/morcenchavez](https://paypal.me/morcenchavez)
- **Buy Me a Coffee**: [buymeacoffee.com/morcenchavez](https://buymeacoffee.com/morcenchavez)

### 🌟 Other Ways to Support
- ⭐ Star this repository
- 🐛 Report bugs and suggest features
- 📢 Share the project with others
- 📝 Contribute to documentation
- 💻 Submit pull requests

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com/) - The PHP framework for web artisans
- Frontend powered by [Vue.js](https://vuejs.org/) - The progressive JavaScript framework
- UI components from [Reka UI](https://reka-ui.com/) - Unstyled, accessible components
- Icons by [Lucide](https://lucide.dev/) - Beautiful & consistent icon toolkit

---

<div align="center">
  <p>Made with ❤️ by the Re:do team</p>
  <p>
    <a href="https://github.com/morcen/redo/issues">Report Bug</a> •
    <a href="https://github.com/morcen/redo/issues">Request Feature</a> •
    <a href="https://github.com/morcen/redo/discussions">Discussions</a>
  </p>
</div>
