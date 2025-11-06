<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\BoardShare;
use App\Models\Card;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create realistic team members
        $users = [
            [
                'name' => 'Sarah Chen',
                'email' => 'sarah.chen@email.com',
            ],
            [
                'name' => 'Marcus Rodriguez',
                'email' => 'marcus.rodriguez@email.com',
            ],
            [
                'name' => 'Emily Johnson',
                'email' => 'emily.johnson@email.com',
            ],
            [
                'name' => 'David Kim',
                'email' => 'david.kim@email.com',
            ],
            [
                'name' => 'Alex Thompson',
                'email' => 'alex.thompson@email.com',
            ],
            [
                'name' => 'Lisa Park',
                'email' => 'lisa.park@email.com',
            ],
            [
                'name' => 'James Wilson',
                'email' => 'james.wilson@email.com',
            ],
            [
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@email.com',
            ],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $createdUsers[] = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => bcrypt('password'), // Default password for all users
            ]);
        }

        // Create boards for different aspects of the project
        $boards = [
            [
                'name' => 'CodeCollab - Core Platform',
                'description' => 'Main development board for core platform features',
                'owner' => $createdUsers[0], // Sarah Chen (Project Manager)
            ],
            [
                'name' => 'CodeCollab - Mobile App',
                'description' => 'React Native mobile application development',
                'owner' => $createdUsers[3], // David Kim (Mobile Developer)
            ],
            [
                'name' => 'CodeCollab - DevOps & Infrastructure',
                'description' => 'CI/CD, deployment, monitoring, and infrastructure tasks',
                'owner' => $createdUsers[6], // James Wilson (DevOps Engineer)
            ],
            [
                'name' => 'CodeCollab - UI/UX Design',
                'description' => 'Design system, user research, and interface improvements',
                'owner' => $createdUsers[5], // Lisa Park (UX Designer)
            ],
        ];

        $createdBoards = [];
        foreach ($boards as $boardData) {
            $board = Board::create([
                'name' => $boardData['name'],
                'description' => $boardData['description'],
                'user_id' => $boardData['owner']->id,
            ]);

            $createdBoards[] = $board;

            // Share boards with relevant team members
            $this->shareBoardWithTeam($board, $createdUsers);
        }

        // Create columns for each board
        $this->createBoardColumns($createdBoards);

        // Create realistic cards for each board
        $this->createCards($createdBoards, $createdUsers);

        // Add comments to cards
        $this->createComments($createdUsers);
    }

    private function shareBoardWithTeam($board, $users)
    {
        // Share each board with most team members (excluding owner)
        $excludeOwners = [$board->user_id];

        foreach ($users as $user) {
            if (! in_array($user->id, $excludeOwners)) {
                BoardShare::create([
                    'board_id' => $board->id,
                    'user_id' => $user->id,
                ]);
            }
        }
    }

    private function createBoardColumns($boards)
    {
        $standardColumns = [
            ['name' => 'To Do', 'position' => 0],
            ['name' => 'In Progress', 'position' => 1],
            ['name' => 'Code Review', 'position' => 2],
            ['name' => 'Testing', 'position' => 3],
            ['name' => 'Done', 'position' => 4],
        ];

        foreach ($boards as $board) {
            foreach ($standardColumns as $columnData) {
                BoardColumn::create([
                    'name' => $columnData['name'],
                    'position' => $columnData['position'],
                    'board_id' => $board->id,
                ]);
            }
        }
    }

    private function createCards($boards, $users)
    {
        // Core Platform Cards
        $corePlatformCards = [
            [
                'title' => 'Implement Code Review System',
                'description' => 'Build pull request review interface with inline comments, code suggestions, and approval workflow. Support syntax highlighting for multiple languages.',
                'column' => 'In Progress',
                'assignee' => $users[1], // Marcus Rodriguez (Backend Developer)
            ],
            [
                'title' => 'Build Real-time Code Collaboration',
                'description' => 'Implement WebSocket connections for live code editing with multiple cursors, conflict resolution, and presence indicators showing who\'s viewing files.',
                'column' => 'To Do',
                'assignee' => $users[2], // Emily Johnson (Full-stack Developer)
            ],
            [
                'title' => 'Create Repository Management',
                'description' => 'Add Git repository integration with branch management, commit history visualization, and merge conflict resolution tools.',
                'column' => 'To Do',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Implement File Diff Viewer',
                'description' => 'Build side-by-side and unified diff views with syntax highlighting, line-by-line comments, and ability to stage/unstage changes.',
                'column' => 'Code Review',
                'assignee' => $users[4], // Alex Thompson (Frontend Developer)
            ],
            [
                'title' => 'Add Issue Tracking Integration',
                'description' => 'Connect code changes to issues with automatic linking, status updates on merge, and issue creation from code comments.',
                'column' => 'Testing',
                'assignee' => $users[4], // Alex Thompson
            ],
            [
                'title' => 'Create API Rate Limiting',
                'description' => 'Implement proper rate limiting for API endpoints to prevent abuse and ensure fair usage across all users.',
                'column' => 'Done',
                'assignee' => $users[6], // James Wilson (DevOps)
            ],
            [
                'title' => 'Implement Code Search',
                'description' => 'Add full-text search across repositories with regex support, file type filters, and search result highlighting.',
                'column' => 'To Do',
                'assignee' => $users[2], // Emily Johnson
            ],
            [
                'title' => 'Add CI/CD Pipeline Builder',
                'description' => 'Create visual pipeline editor for configuring build, test, and deployment workflows with YAML export.',
                'column' => 'In Progress',
                'assignee' => $users[4], // Alex Thompson
            ],
            [
                'title' => 'Implement Activity Feed',
                'description' => 'Track and display all repository activities including commits, pull requests, merges, and team member actions.',
                'column' => 'Code Review',
                'assignee' => $users[1], // Marcus Rodriguez
            ],
            [
                'title' => 'Add Code Snippet Sharing',
                'description' => 'Allow users to create and share reusable code snippets with syntax highlighting, tags, and version history.',
                'column' => 'Testing',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Implement Branch Protection Rules',
                'description' => 'Create configurable rules for branch protection including required reviews, status checks, and merge restrictions.',
                'column' => 'Done',
                'assignee' => $users[0], // Sarah Chen (Project Manager)
            ],
            [
                'title' => 'Add Keyboard Shortcuts',
                'description' => 'Implement keyboard shortcuts for common actions like creating branches, opening files, and navigating code.',
                'column' => 'To Do',
                'assignee' => $users[4], // Alex Thompson
            ],
        ];

        // Mobile App Cards
        $mobileAppCards = [
            [
                'title' => 'Implement Offline Code Viewing',
                'description' => 'Allow users to view code files and repositories when offline. Cache recently viewed files and sync changes when connection is restored.',
                'column' => 'In Progress',
                'assignee' => $users[3], // David Kim (Mobile Developer)
            ],
            [
                'title' => 'Add Push Notifications for PRs',
                'description' => 'Notify users about pull request reviews, merge requests, code comments, and build status updates. Include notification preferences.',
                'column' => 'To Do',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Optimize App Performance',
                'description' => 'Reduce bundle size, implement lazy loading for large files, and optimize syntax highlighting rendering for better performance.',
                'column' => 'Testing',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Add Biometric Authentication',
                'description' => 'Implement fingerprint and face ID authentication for mobile app security when accessing repositories.',
                'column' => 'Code Review',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Implement Mobile Code Editor',
                'description' => 'Add touch-optimized code editor with syntax highlighting, auto-completion, and basic editing capabilities for quick fixes on mobile.',
                'column' => 'To Do',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Add Swipe Gestures for Navigation',
                'description' => 'Implement swipe gestures for navigating between files, switching branches, and dismissing code review comments.',
                'column' => 'In Progress',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Create Home Screen Widgets',
                'description' => 'Add iOS and Android widgets showing repository status, open pull requests count, and recent commits.',
                'column' => 'Code Review',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Implement Dark Mode',
                'description' => 'Add system-aware dark mode support with syntax highlighting optimized for dark themes and smooth theme transitions.',
                'column' => 'Testing',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Add Quick Actions',
                'description' => 'Implement quick actions for common tasks like creating branches, approving PRs, and viewing diffs from notifications.',
                'column' => 'To Do',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Optimize for Tablets',
                'description' => 'Enhance tablet experience with split-screen code viewing, multi-file tabs, and larger touch targets for better code navigation.',
                'column' => 'Done',
                'assignee' => $users[3], // David Kim
            ],
        ];

        // DevOps Cards
        $devOpsCards = [
            [
                'title' => 'Set up Kubernetes Cluster',
                'description' => 'Deploy CodeCollab application to Kubernetes with proper scaling, health checks, and resource limits for Git operations.',
                'column' => 'To Do',
                'assignee' => $users[6], // James Wilson (DevOps)
            ],
            [
                'title' => 'Implement CI/CD Pipeline',
                'description' => 'Create automated testing, building, and deployment pipeline with GitHub Actions for all repository integrations.',
                'column' => 'In Progress',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Set up Monitoring & Alerting',
                'description' => 'Configure Prometheus, Grafana, and alerting for application performance, Git operation metrics, and error tracking.',
                'column' => 'Code Review',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Database Backup Strategy',
                'description' => 'Implement automated daily backups for repository metadata, user data, and code review history with point-in-time recovery.',
                'column' => 'Testing',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Implement Blue-Green Deployment',
                'description' => 'Set up zero-downtime deployment strategy for code collaboration features with automatic rollback capabilities.',
                'column' => 'To Do',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Configure Auto-scaling',
                'description' => 'Implement horizontal pod autoscaling based on CPU, memory usage, and concurrent Git operation metrics.',
                'column' => 'In Progress',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Set up Log Aggregation',
                'description' => 'Implement centralized logging with ELK stack for Git operations, API requests, and code review activities.',
                'column' => 'Code Review',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Implement Security Scanning',
                'description' => 'Add automated security scanning for dependencies, container vulnerabilities, and code security analysis.',
                'column' => 'Testing',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Create Disaster Recovery Plan',
                'description' => 'Document and test disaster recovery procedures for repository data and user code with RTO/RPO targets.',
                'column' => 'Done',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Optimize Git Storage Performance',
                'description' => 'Implement Git repository storage optimization, indexing for large repos, and connection pooling for Git operations.',
                'column' => 'To Do',
                'assignee' => $users[6], // James Wilson
            ],
        ];

        // UI/UX Design Cards
        $designCards = [
            [
                'title' => 'Create Design System Documentation',
                'description' => 'Document all components, colors, typography, and spacing guidelines for consistent design across code editor, diff views, and repository interfaces.',
                'column' => 'Code Review',
                'assignee' => $users[5], // Lisa Park (UX Designer)
            ],
            [
                'title' => 'Conduct User Research for Code Review',
                'description' => 'Interview 10 developers to understand their code review workflow, pain points with current interface, and desired improvements.',
                'column' => 'To Do',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Design Dark Mode Theme',
                'description' => 'Create comprehensive dark mode design for code editor, syntax highlighting, and all UI components optimized for extended coding sessions.',
                'column' => 'In Progress',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Redesign Repository Onboarding',
                'description' => 'Simplify the new user onboarding process for connecting repositories with better visual guidance and step-by-step instructions.',
                'column' => 'Testing',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Accessibility Audit & Improvements',
                'description' => 'Audit current interface for WCAG compliance, especially for code viewing and navigation, and implement necessary improvements.',
                'column' => 'Done',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Create Developer User Journey Maps',
                'description' => 'Map out complete user journeys from repository connection to code review, merge, and collaboration workflows.',
                'column' => 'To Do',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Design Mobile Code Viewing Components',
                'description' => 'Create mobile-optimized versions of code viewer, diff display, and file navigation with touch-friendly interactions.',
                'column' => 'In Progress',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Implement Code Review Micro-interactions',
                'description' => 'Add subtle animations for code highlighting, comment threads, merge animations, and status updates to enhance developer experience.',
                'column' => 'Code Review',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Create Brand Guidelines',
                'description' => 'Establish comprehensive brand guidelines for CodeCollab including logo usage, color palettes optimized for code viewing, and developer-focused voice.',
                'column' => 'Testing',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Design Error States & Empty States',
                'description' => 'Create helpful and engaging designs for error messages (merge conflicts, build failures) and empty repository states.',
                'column' => 'Done',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Conduct A/B Testing for Code Review UI',
                'description' => 'Set up testing framework for different code review interface variations, comment placement, and diff view layouts.',
                'column' => 'To Do',
                'assignee' => $users[5], // Lisa Park
            ],
        ];

        $allCards = [
            $corePlatformCards,
            $mobileAppCards,
            $devOpsCards,
            $designCards,
        ];

        foreach ($boards as $boardIndex => $board) {
            $columns = $board->columns()->orderBy('position')->get();
            $cards = $allCards[$boardIndex];

            foreach ($cards as $cardIndex => $cardData) {
                $column = $columns->where('name', $cardData['column'])->first();

                if ($column) {
                    Card::create([
                        'title' => $cardData['title'],
                        'description' => $cardData['description'],
                        'board_id' => $board->id,
                        'board_column_id' => $column->id,
                        'user_id' => $cardData['assignee']->id,
                        'position' => $cardIndex,
                    ]);
                }
            }
        }
    }

    private function createComments($users)
    {
        // Get all cards to add comments to
        $cards = Card::all();

        // Define realistic comment templates for different scenarios
        $commentTemplates = [
            // Technical discussions
            [
                'content' => 'I think we should consider using Redis for caching the real-time updates. It would be much more efficient than polling.',
                'context' => 'technical',
            ],
            [
                'content' => 'The API response time is looking good, but we might want to add some pagination for large datasets.',
                'context' => 'technical',
            ],
            [
                'content' => 'I\'ve tested this on Chrome, Firefox, and Safari. Everything looks good across all browsers.',
                'context' => 'testing',
            ],
            [
                'content' => 'Found a small bug in the mobile version. The drag and drop doesn\'t work properly on iOS Safari.',
                'context' => 'bug',
            ],
            [
                'content' => 'This looks great! The UI is much cleaner now. Should we also update the mobile version?',
                'context' => 'feedback',
            ],
            [
                'content' => 'I\'ve updated the documentation in the wiki. The API endpoints are now fully documented.',
                'context' => 'documentation',
            ],
            [
                'content' => 'Can we schedule a quick call to discuss the implementation approach? I have some questions about the architecture.',
                'context' => 'meeting',
            ],
            [
                'content' => 'I\'ve created a branch for this feature. The code is ready for review.',
                'context' => 'development',
            ],
            [
                'content' => 'The performance tests are passing. We\'re meeting all our benchmarks.',
                'context' => 'testing',
            ],
            [
                'content' => 'I\'ve deployed this to staging. You can test it at staging.example.com',
                'context' => 'deployment',
            ],
            [
                'content' => 'Great work on this! The implementation is exactly what we discussed.',
                'context' => 'praise',
            ],
            [
                'content' => 'I noticed we might need to handle edge cases for users with slow connections.',
                'context' => 'consideration',
            ],
            [
                'content' => 'The design looks perfect! This matches our brand guidelines exactly.',
                'context' => 'design',
            ],
            [
                'content' => 'I\'ve added some unit tests for this functionality. Coverage is now at 95%.',
                'context' => 'testing',
            ],
            [
                'content' => 'Can we add some error handling for when the API is down?',
                'context' => 'improvement',
            ],
            [
                'content' => 'I\'ve updated the user story to include the new requirements from the client.',
                'context' => 'requirements',
            ],
            [
                'content' => 'The accessibility audit passed! We\'re now WCAG 2.1 AA compliant.',
                'context' => 'accessibility',
            ],
            [
                'content' => 'I\'ve optimized the database queries. The page load time improved by 40%.',
                'context' => 'optimization',
            ],
            [
                'content' => 'The security scan came back clean. No vulnerabilities found.',
                'context' => 'security',
            ],
            [
                'content' => 'I\'ve created a demo video showing the new features. Check it out!',
                'context' => 'demo',
            ],
        ];

        // Add comments to cards
        foreach ($cards as $card) {
            // Randomly decide how many comments to add (1-4 comments per card)
            $commentCount = rand(1, 4);

            for ($i = 0; $i < $commentCount; $i++) {
                // Randomly select a user (excluding the card assignee sometimes for variety)
                $commentUser = $users[array_rand($users)];

                // Sometimes use a different user than the assignee
                if (rand(1, 3) === 1 && $card->user_id !== $commentUser->id) {
                    // Use a different user
                } else {
                    // Use the card assignee - find the user by ID in the array
                    $assigneeUser = collect($users)->where('id', $card->user_id)->first();
                    $commentUser = $assigneeUser ?? $users[array_rand($users)];
                }

                // Select a random comment template
                $template = $commentTemplates[array_rand($commentTemplates)];

                // Create the comment
                Comment::create([
                    'content' => $template['content'],
                    'card_id' => $card->id,
                    'user_id' => $commentUser->id,
                    'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                ]);
            }
        }
    }
}
