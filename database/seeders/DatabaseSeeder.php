<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\GeneratedReport;
use App\Models\Keyword;
use App\Models\KeywordRanking;
use App\Models\Project;
use App\Models\ReportTemplate;
use App\Models\Snapshot;
use App\Services\VisibilityScoreService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding RankReport Pro demo data...');

        // ── Create admin user ─────────────────────────────────────────────
        DB::table('users')->insertOrIgnore([
            'name'              => 'Admin',
            'email'             => 'admin@rankreport.pro',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // ── Create default report template ────────────────────────────────
        $template = ReportTemplate::firstOrCreate(
            ['name' => 'Default Agency Template'],
            [
                'cover_title'        => 'SEO Performance Report',
                'agency_name'        => 'RankReport Pro Agency',
                'primary_color'      => '#3c8dbc',
                'secondary_color'    => '#ffffff',
                'is_default'         => true,
                'layout_config_json' => [
                    'sections' => [
                        'cover', 'executive_summary', 'kpi_summary',
                        'position_chart', 'distribution_chart', 'top_keywords',
                        'landing_pages', 'competitor_monitoring', 'action_items',
                    ]
                ],
            ]
        );

        // ── Create demo client ────────────────────────────────────────────
        $client = Client::firstOrCreate(
            ['domain' => 'vnptai.io'],
            [
                'name'             => 'VNPT AI',
                'company_name'     => 'VNPT AI Corporation',
                'contact_name'     => 'Nguyen Van A',
                'contact_email'    => 'seo@vnptai.io',
                'report_frequency' => 'monthly',
                'notes'            => 'Demo client được tạo tự động.',
            ]
        );

        // ── Main project ──────────────────────────────────────────────────
        $mainProject = Project::firstOrCreate(
            ['domain' => 'vnptai.io', 'client_id' => $client->id, 'project_type' => 'main'],
            [
                'name'            => 'VNPT AI - Main',
                'country_code'    => 'VN',
                'device_type'     => 'desktop',
                'status'          => 'active',
                'is_main_project' => true,
            ]
        );

        // ── Competitor projects ───────────────────────────────────────────
        $competitorDomains = [
            ['domain' => 'fpt.ai', 'name' => 'FPT AI'],
            ['domain' => 'viettel.ai', 'name' => 'Viettel AI'],
        ];

        $competitorProjectIds = [];
        foreach ($competitorDomains as $cd) {
            $comp = Project::firstOrCreate(
                ['domain' => $cd['domain'], 'client_id' => $client->id],
                [
                    'name'         => $cd['name'],
                    'project_type' => 'competitor',
                    'country_code' => 'VN',
                    'device_type'  => 'desktop',
                    'status'       => 'active',
                ]
            );
            $competitorProjectIds[] = $comp->id;
        }

        // Assign competitors to main project
        $mainProject->competitors()->sync($competitorProjectIds);

        // ── Create 3 snapshots for the main project ───────────────────────
        $snapshotDates = [
            Carbon::now()->subMonths(2)->format('Y-m-d'),
            Carbon::now()->subMonth()->format('Y-m-d'),
            Carbon::now()->format('Y-m-d'),
        ];

        $this->command->info('Generating demo snapshots and keyword rankings...');

        $prevPositions = [];
        foreach ($snapshotDates as $idx => $date) {
            $snapshot = Snapshot::firstOrCreate(
                ['project_id' => $mainProject->id, 'report_date' => $date],
                [
                    'snapshot_name'  => "Ahrefs Export – vnptai.io ({$date})",
                    'snapshot_type'  => 'ahrefs',
                    'status'         => 'completed',
                    'total_keywords' => 0,
                ]
            );

            if ($snapshot->wasRecentlyCreated) {
                $count = $this->seedKeywordsForSnapshot($snapshot, $mainProject, $prevPositions);
                $snapshot->update(['total_keywords' => $count]);
                $this->command->info("  → Snapshot {$date}: {$count} keywords");
            }

            // Build prevPositions map for next iteration
            $prevPositions = DB::table('keyword_rankings as kr')
                ->join('keywords as k', 'kr.keyword_id', '=', 'k.id')
                ->where('kr.snapshot_id', $snapshot->id)
                ->pluck('kr.current_position', 'k.normalized_keyword')
                ->toArray();
        }

        $this->command->info('Done! Login: admin@rankreport.pro / password');
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Seed realistic keyword data for a snapshot.
     */
    private function seedKeywordsForSnapshot(
        Snapshot $snapshot,
        Project  $project,
        array    $prevPositions
    ): int {

        $visibilityService = new VisibilityScoreService();

        // Sample keyword pool (would be much larger in real use)
        $kwPool = [
            ['kw' => 'vnpt ai',        'vol' => 2400, 'base_pos' => 1,  'brand' => true],
            ['kw' => 'giải pháp ai',   'vol' => 1900, 'base_pos' => 3,  'brand' => false],
            ['kw' => 'nhận dạng giọng nói', 'vol' => 3200, 'base_pos' => 5, 'brand' => false],
            ['kw' => 'trí tuệ nhân tạo', 'vol' => 8100, 'base_pos' => 8, 'brand' => false],
            ['kw' => 'chatbot ai',     'vol' => 5400, 'base_pos' => 12, 'brand' => false],
            ['kw' => 'ocr tiếng việt', 'vol' => 1600, 'base_pos' => 2,  'brand' => false],
            ['kw' => 'khai thác dữ liệu', 'vol' => 720, 'base_pos' => 15, 'brand' => false],
            ['kw' => 'ai speech to text', 'vol' => 2900, 'base_pos' => 7, 'brand' => false],
            ['kw' => 'phân tích văn bản',  'vol' => 1100, 'base_pos' => 9, 'brand' => false],
            ['kw' => 'nhận diện khuôn mặt', 'vol' => 4200, 'base_pos' => 6, 'brand' => false],
            ['kw' => 'machine learning việt nam', 'vol' => 3600, 'base_pos' => 11, 'brand' => false],
            ['kw' => 'nlp tiếng việt',  'vol' => 880,  'base_pos' => 4,  'brand' => false],
            ['kw' => 'deep learning',   'vol' => 6700, 'base_pos' => 18, 'brand' => false],
            ['kw' => 'computer vision', 'vol' => 3100, 'base_pos' => 22, 'brand' => false],
            ['kw' => 'text to speech vn', 'vol' => 1500, 'base_pos' => 13, 'brand' => false],
            ['kw' => 'invoice ai ocr',  'vol' => 590,  'base_pos' => 16, 'brand' => false],
            ['kw' => 'virtual assistant', 'vol' => 4800, 'base_pos' => 25, 'brand' => false],
            ['kw' => 'predictive analytics', 'vol' => 2200, 'base_pos' => 30, 'brand' => false],
            ['kw' => 'ai platform vietnam', 'vol' => 1300, 'base_pos' => 35, 'brand' => false],
            ['kw' => 'smart city ai',   'vol' => 890,  'base_pos' => 45, 'brand' => false],
        ];

        $now = now()->toDateTimeString();
        $inserted = 0;

        foreach ($kwPool as $kwData) {
            $normalized = Keyword::normalize($kwData['kw']);

            // Upsert keyword
            $kwRecord = Keyword::firstOrCreate(
                ['project_id' => $project->id, 'normalized_keyword' => $normalized],
                [
                    'keyword'    => $kwData['kw'],
                    'brand_flag' => $kwData['brand'],
                ]
            );

            // Slightly randomize position to simulate changes
            $variation   = rand(-3, 3);
            $currentPos  = max(1, min(100, $kwData['base_pos'] + $variation));
            $prevPos     = $prevPositions[$normalized] ?? null;
            $posChange   = $prevPos !== null ? ($prevPos - $currentPos) : 0;

            KeywordRanking::firstOrCreate(
                ['snapshot_id' => $snapshot->id, 'keyword_id' => $kwRecord->id],
                [
                    'current_position'  => $currentPos,
                    'previous_position' => $prevPos,
                    'position_change'   => $posChange,
                    'search_volume'     => $kwData['vol'],
                    'target_url'        => 'https://vnptai.io/' . str_replace(' ', '-', $kwData['kw']),
                    'visibility_points' => $visibilityService->calculate($currentPos),
                    'raw_data_json'     => null,
                ]
            );
            $inserted++;
        }

        return $inserted;
    }
}
