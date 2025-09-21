<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use App\Models\BoardShare;
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
                'email' => 'sarah.chen@taskflow.com',
            ],
            [
                'name' => 'Marcus Rodriguez',
                'email' => 'marcus.rodriguez@taskflow.com',
            ],
            [
                'name' => 'Emily Johnson',
                'email' => 'emily.johnson@taskflow.com',
            ],
            [
                'name' => 'David Kim',
                'email' => 'david.kim@taskflow.com',
            ],
            [
                'name' => 'Alex Thompson',
                'email' => 'alex.thompson@taskflow.com',
            ],
            [
                'name' => 'Lisa Park',
                'email' => 'lisa.park@taskflow.com',
            ],
            [
                'name' => 'James Wilson',
                'email' => 'james.wilson@taskflow.com',
            ],
            [
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@taskflow.com',
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
                'name' => 'TaskFlow Pro - Core Platform',
                'description' => 'Main development board for core platform features',
                'owner' => $createdUsers[0], // Sarah Chen (Project Manager)
            ],
            [
                'name' => 'TaskFlow Pro - Mobile App',
                'description' => 'React Native mobile application development',
                'owner' => $createdUsers[3], // David Kim (Mobile Developer)
            ],
            [
                'name' => 'TaskFlow Pro - DevOps & Infrastructure',
                'description' => 'CI/CD, deployment, monitoring, and infrastructure tasks',
                'owner' => $createdUsers[6], // James Wilson (DevOps Engineer)
            ],
            [
                'name' => 'TaskFlow Pro - UI/UX Design',
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
    }

    private function shareBoardWithTeam($board, $users)
    {
        // Share each board with most team members (excluding owner)
        $excludeOwners = [$board->user_id];
        
        foreach ($users as $user) {
            if (!in_array($user->id, $excludeOwners)) {
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
                'title' => 'Implement OAuth 2.0 Authentication',
                'description' => 'Set up Google, GitHub, and Microsoft OAuth providers for user authentication. Include proper token refresh logic and security measures.',
                'column' => 'In Progress',
                'assignee' => $users[1], // Marcus Rodriguez (Backend Developer)
            ],
            [
                'title' => 'Build Real-time Collaboration System',
                'description' => 'Implement WebSocket connections for real-time updates when multiple users are editing the same board simultaneously.',
                'column' => 'To Do',
                'assignee' => $users[2], // Emily Johnson (Full-stack Developer)
            ],
            [
                'title' => 'Create Advanced Filtering System',
                'description' => 'Add filters for cards by assignee, due date, labels, and custom fields. Include saved filter presets.',
                'column' => 'To Do',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Implement File Attachments',
                'description' => 'Allow users to attach files to cards with drag-and-drop functionality. Include image previews and file size limits.',
                'column' => 'Code Review',
                'assignee' => $users[4], // Alex Thompson (Frontend Developer)
            ],
            [
                'title' => 'Add Bulk Operations',
                'description' => 'Enable bulk editing of cards - move multiple cards, assign to users, add labels, etc.',
                'column' => 'Testing',
                'assignee' => $users[4], // Alex Thompson
            ],
            [
                'title' => 'Create API Rate Limiting',
                'description' => 'Implement proper rate limiting for API endpoints to prevent abuse and ensure fair usage.',
                'column' => 'Done',
                'assignee' => $users[6], // James Wilson (DevOps)
            ],
            [
                'title' => 'Implement Search Functionality',
                'description' => 'Add global search across boards, cards, and descriptions with advanced query capabilities.',
                'column' => 'To Do',
                'assignee' => $users[2], // Emily Johnson
            ],
            [
                'title' => 'Add Card Templates',
                'description' => 'Create reusable card templates for common task types to speed up project setup.',
                'column' => 'In Progress',
                'assignee' => $users[4], // Alex Thompson
            ],
            [
                'title' => 'Implement Activity Feed',
                'description' => 'Track and display all board activities including card moves, assignments, and comments.',
                'column' => 'Code Review',
                'assignee' => $users[1], // Marcus Rodriguez
            ],
            [
                'title' => 'Add Custom Fields to Cards',
                'description' => 'Allow users to add custom fields like priority, effort estimation, and due dates.',
                'column' => 'Testing',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Implement Board Templates',
                'description' => 'Create predefined board layouts for different project types (Sprint, Bug Tracking, etc.).',
                'column' => 'Done',
                'assignee' => $users[0], // Sarah Chen (Project Manager)
            ],
            [
                'title' => 'Add Keyboard Shortcuts',
                'description' => 'Implement keyboard shortcuts for common actions like creating cards, moving between boards.',
                'column' => 'To Do',
                'assignee' => $users[4], // Alex Thompson
            ],
        ];

        // Mobile App Cards
        $mobileAppCards = [
            [
                'title' => 'Implement Offline Mode',
                'description' => 'Allow users to view and edit boards when offline. Sync changes when connection is restored.',
                'column' => 'In Progress',
                'assignee' => $users[3], // David Kim (Mobile Developer)
            ],
            [
                'title' => 'Add Push Notifications',
                'description' => 'Notify users about card assignments, due dates, and mentions. Include notification preferences.',
                'column' => 'To Do',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Optimize App Performance',
                'description' => 'Reduce bundle size, implement lazy loading, and optimize image handling for better performance.',
                'column' => 'Testing',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Add Biometric Authentication',
                'description' => 'Implement fingerprint and face ID authentication for mobile app security.',
                'column' => 'Code Review',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Implement Gesture Navigation',
                'description' => 'Add swipe gestures for card navigation and board switching for better mobile UX.',
                'column' => 'To Do',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Add Haptic Feedback',
                'description' => 'Implement haptic feedback for card interactions, drag operations, and notifications.',
                'column' => 'In Progress',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Create Widget Support',
                'description' => 'Add iOS and Android home screen widgets showing board overview and quick actions.',
                'column' => 'Code Review',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Implement Dark Mode',
                'description' => 'Add system-aware dark mode support with smooth theme transitions.',
                'column' => 'Testing',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Add Voice Commands',
                'description' => 'Implement voice-to-text for card creation and basic voice navigation commands.',
                'column' => 'To Do',
                'assignee' => $users[3], // David Kim
            ],
            [
                'title' => 'Optimize for Tablets',
                'description' => 'Enhance tablet experience with multi-column layouts and larger touch targets.',
                'column' => 'Done',
                'assignee' => $users[3], // David Kim
            ],
        ];

        // DevOps Cards
        $devOpsCards = [
            [
                'title' => 'Set up Kubernetes Cluster',
                'description' => 'Deploy application to Kubernetes with proper scaling, health checks, and resource limits.',
                'column' => 'To Do',
                'assignee' => $users[6], // James Wilson (DevOps)
            ],
            [
                'title' => 'Implement CI/CD Pipeline',
                'description' => 'Create automated testing, building, and deployment pipeline with GitHub Actions.',
                'column' => 'In Progress',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Set up Monitoring & Alerting',
                'description' => 'Configure Prometheus, Grafana, and alerting for application performance and error tracking.',
                'column' => 'Code Review',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Database Backup Strategy',
                'description' => 'Implement automated daily backups with point-in-time recovery capabilities.',
                'column' => 'Testing',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Implement Blue-Green Deployment',
                'description' => 'Set up zero-downtime deployment strategy with automatic rollback capabilities.',
                'column' => 'To Do',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Configure Auto-scaling',
                'description' => 'Implement horizontal pod autoscaling based on CPU and memory usage metrics.',
                'column' => 'In Progress',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Set up Log Aggregation',
                'description' => 'Implement centralized logging with ELK stack for better debugging and analysis.',
                'column' => 'Code Review',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Implement Security Scanning',
                'description' => 'Add automated security scanning for dependencies and container vulnerabilities.',
                'column' => 'Testing',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Create Disaster Recovery Plan',
                'description' => 'Document and test disaster recovery procedures with RTO/RPO targets.',
                'column' => 'Done',
                'assignee' => $users[6], // James Wilson
            ],
            [
                'title' => 'Optimize Database Performance',
                'description' => 'Implement database indexing, query optimization, and connection pooling.',
                'column' => 'To Do',
                'assignee' => $users[6], // James Wilson
            ],
        ];

        // UI/UX Design Cards
        $designCards = [
            [
                'title' => 'Create Design System Documentation',
                'description' => 'Document all components, colors, typography, and spacing guidelines for consistent design.',
                'column' => 'Code Review',
                'assignee' => $users[5], // Lisa Park (UX Designer)
            ],
            [
                'title' => 'Conduct User Research for Mobile',
                'description' => 'Interview 10 mobile users to understand their workflow and pain points with current mobile experience.',
                'column' => 'To Do',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Design Dark Mode Theme',
                'description' => 'Create comprehensive dark mode design for all components and pages.',
                'column' => 'In Progress',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Redesign Onboarding Flow',
                'description' => 'Simplify the new user onboarding process with better visual guidance and fewer steps.',
                'column' => 'Testing',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Accessibility Audit & Improvements',
                'description' => 'Audit current interface for WCAG compliance and implement necessary improvements.',
                'column' => 'Done',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Create User Journey Maps',
                'description' => 'Map out complete user journeys from signup to advanced board management.',
                'column' => 'To Do',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Design Mobile-First Components',
                'description' => 'Create mobile-optimized versions of all UI components with touch-friendly interactions.',
                'column' => 'In Progress',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Implement Micro-interactions',
                'description' => 'Add subtle animations and transitions to enhance user experience and provide feedback.',
                'column' => 'Code Review',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Create Brand Guidelines',
                'description' => 'Establish comprehensive brand guidelines including logo usage, color palettes, and voice.',
                'column' => 'Testing',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Design Error States & Empty States',
                'description' => 'Create helpful and engaging designs for error messages and empty board states.',
                'column' => 'Done',
                'assignee' => $users[5], // Lisa Park
            ],
            [
                'title' => 'Conduct A/B Testing Framework',
                'description' => 'Set up testing framework for different UI variations and user flows.',
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
}
