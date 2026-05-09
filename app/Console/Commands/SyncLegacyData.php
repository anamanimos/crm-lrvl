<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;
use App\Models\Label;
use App\Models\Customer;

#[Signature('app:sync-legacy-data')]
#[Description('Sync data from legacy CI3 database to Laravel database')]
class SyncLegacyData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting legacy data sync...');

        // 1. Sync Labels
        $this->info('Syncing Labels...');
        $legacyLabels = DB::connection('legacy')->table('labels')->get();
        foreach ($legacyLabels as $label) {
            Label::updateOrCreate(
                ['id' => $label->id],
                [
                    'name' => $label->name,
                    'color' => $label->color,
                    'is_active' => $label->is_active,
                    'created_at' => $label->created_at ?? now(),
                    'updated_at' => $label->updated_at ?? now(),
                ]
            );
        }
        $this->info('Labels synced: ' . $legacyLabels->count());

        // 2. Sync Customers
        $this->info('Syncing Customers...');
        $legacyCustomers = DB::connection('legacy')->table('customers')->get();
        foreach ($legacyCustomers as $customer) {
            Customer::updateOrCreate(
                ['id' => $customer->id],
                [
                    'uuid' => $customer->uuid,
                    'company_id' => $customer->company_id,
                    'wa_number' => $customer->wa_number,
                    'lid' => $customer->lid,
                    'name' => $customer->name,
                    'avatar' => $customer->avatar,
                    'avatar_last_updated' => $customer->avatar_last_updated,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'dob' => $customer->dob,
                    'gender' => $customer->gender,
                    'assigned_user_id' => $customer->assigned_user_id,
                    'notes' => $customer->notes,
                    'last_chat_at' => $customer->last_chat_at,
                    'is_archived' => $customer->is_archived,
                    'created_at' => $customer->created_at,
                    'updated_at' => $customer->updated_at,
                ]
            );
        }
        $this->info('Customers synced: ' . $legacyCustomers->count());

        // 3. Sync Customer Labels (Pivot)
        $this->info('Syncing Customer Labels pivot...');
        DB::table('customer_labels')->truncate();
        $legacyPivot = DB::connection('legacy')->table('customer_labels')->get();
        foreach ($legacyPivot as $pivot) {
            DB::table('customer_labels')->insert([
                'customer_id' => $pivot->customer_id,
                'label_id' => $pivot->label_id,
                'customer_uuid' => $pivot->customer_uuid,
            ]);
        }
        $this->info('Pivot data synced: ' . $legacyPivot->count());

        // 4. Sync Users
        $this->info('Syncing Users...');
        $legacyUsers = DB::connection('legacy')->table('users')->get();
        foreach ($legacyUsers as $user) {
            DB::table('users')->updateOrInsert(
                ['id' => $user->id],
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password, // Password hashing might be different, but CI3 often uses bcrypt too
                    'role' => $user->role ?? 'user',
                    'is_active' => $user->is_active ?? true,
                    'created_at' => $user->created_at ?? now(),
                    'updated_at' => $user->updated_at ?? now(),
                ]
            );
        }
        $this->info('Users synced: ' . $legacyUsers->count());

        $this->info('Syncing Deal Stages...');
        $legacyStages = DB::connection('legacy')->table('deal_stages')->get();
        foreach ($legacyStages as $stage) {
            DB::table('deal_stages')->updateOrInsert(
                ['id' => $stage->id],
                [
                    'name' => $stage->name,
                    'color' => $stage->color,
                    'stage_type' => $stage->stage_type,
                    'sort_order' => $stage->sort_order,
                    'is_active' => $stage->is_active,
                ]
            );
        }
        $this->info('Deal Stages synced: ' . $legacyStages->count());

        // 5. Sync Deals
        $this->info('Syncing Deals...');
        $legacyDeals = DB::connection('legacy')->table('deals')->get();
        foreach ($legacyDeals as $deal) {
            if (!$deal->customer_id || !$deal->deal_stage_id) {
                $this->warn("Skipping Deal ID {$deal->id} due to missing customer_id or deal_stage_id");
                continue;
            }
            DB::table('deals')->updateOrInsert(

                ['id' => $deal->id],
                [
                    'uuid' => $deal->uuid,
                    'title' => $deal->title,
                    'customer_id' => $deal->customer_id,
                    'deal_stage_id' => $deal->deal_stage_id,
                    'expected_value' => $deal->expected_value,
                    'source' => $deal->source,
                    'assigned_user_id' => $deal->assigned_user_id,
                    'next_followup_date' => $deal->next_followup_date,
                    'expected_close_date' => $deal->expected_close_date,
                    'lost_reason' => $deal->lost_reason,
                    'is_archived' => $deal->is_archived,
                    'created_at' => $deal->created_at,
                    'updated_at' => $deal->updated_at,
                ]
            );
        }
        $this->info('Deals synced: ' . $legacyDeals->count());

        // 6. Sync Companies
        $this->info('Syncing Companies...');
        $legacyCompanies = DB::connection('legacy')->table('companies')->get();
        foreach ($legacyCompanies as $company) {
            DB::table('companies')->updateOrInsert(
                ['id' => $company->id],
                [
                    'uuid' => $company->uuid,
                    'name' => $company->name,
                    'address' => $company->address,
                    'phone' => $company->phone,
                    'email' => $company->email,
                    'website' => $company->website,
                    'created_at' => $company->created_at,
                    'updated_at' => $company->updated_at,
                ]
            );
        }
        $this->info('Companies synced: ' . $legacyCompanies->count());

        // 7. Sync Messages (Limit 5000 for safety during migration)
        $this->info('Syncing Messages (latest 5000)...');
        $legacyMessages = DB::connection('legacy')->table('messages')->orderBy('id', 'desc')->limit(5000)->get();
        foreach ($legacyMessages as $message) {
            if (!$message->customer_id) {
                $this->warn("Skipping Message ID {$message->id} due to missing customer_id");
                continue;
            }
            DB::table('messages')->updateOrInsert(

                ['id' => $message->id],
                [
                    'uuid' => $message->uuid ?? null,
                    'customer_id' => $message->customer_id,
                    'company_id' => $message->company_id ?? null,
                    'wa_message_id' => $message->message_id ?? null,
                    'wa_timestamp' => $message->wa_timestamp ?? null,

                    'reply_message_id' => $message->reply_message_id,
                    'reply_content' => $message->reply_content,
                    'reply_sender_name' => $message->reply_sender_name,
                    'type' => $message->type,
                    'direction' => $message->direction,
                    'sender_type' => $message->sender_type,
                    'user_id' => $message->user_id,
                    'is_external_reply' => $message->is_external_reply,
                    'content' => $message->content,
                    'media_url' => $message->media_url,
                    'media_path' => $message->media_path,
                    'media_local_path' => $message->media_local_path,
                    'media_meta' => $message->media_meta,
                    'media_status' => $message->media_status,
                    'media_attempts' => $message->media_attempts,
                    'media_last_error' => $message->media_last_error,
                    'media_started_at' => $message->media_started_at,
                    'media_uploaded_at' => $message->media_uploaded_at,
                    'media_log' => $message->media_log,
                    'status' => $message->status,
                    'is_deleted' => $message->is_deleted,
                    'is_edited' => $message->is_edited,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                ]
            );
        }
        $this->info('Messages synced: ' . $legacyMessages->count());
        
        // 8. Sync Templates
        $this->info('Syncing Templates...');
        $legacyTemplates = DB::connection('legacy')->table('message_templates')->get();
        foreach ($legacyTemplates as $template) {
            DB::table('templates')->updateOrInsert(
                ['id' => $template->id],
                [
                    'title' => $template->name,
                    'content' => $template->content,
                    'category' => $template->category,
                    'is_active' => $template->is_active,
                    'created_at' => $template->created_at ?? now(),
                    'updated_at' => $template->updated_at ?? now(),
                ]
            );
        }
        $this->info('Templates synced: ' . $legacyTemplates->count());

        $this->info('Sync completed successfully!');


    }
}

