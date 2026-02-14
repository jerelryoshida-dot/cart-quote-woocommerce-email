<?php
/**
 * Quote Repository
 *
 * Handles all database operations for quote submissions using
 * the repository pattern for clean separation of concerns.
 *
 * @package CartQuoteWooCommerce\Database
 * @author Jerel Yoshida
 * @since 1.0.0
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Database;

use CartQuoteWooCommerce\Core\Debug_Logger;

class Quote_Repository
{
    private \wpdb $wpdb;

    private string $table_name;

    private string $logs_table;

    private Debug_Logger $logger;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . CART_QUOTE_WC_TABLE_SUBMISSIONS;
        $this->logs_table = $wpdb->prefix . 'cart_quote_logs';
        $this->logger = Debug_Logger::get_instance();
    }

    /**
     * Generate a unique quote ID
     *
     * @return string
     */
    public function generate_quote_id(): string
    {
        $prefix = get_option('cart_quote_wc_quote_prefix', 'Q');
        $start_number = (int) get_option('cart_quote_wc_quote_start_number', '1001');
        
        // Get the last quote number
        $last_quote = $this->wpdb->get_var(
            "SELECT quote_id FROM {$this->table_name} ORDER BY id DESC LIMIT 1"
        );

        if ($last_quote && preg_match('/\d+/', $last_quote, $matches)) {
            $next_number = (int) $matches[0] + 1;
        } else {
            $next_number = $start_number;
        }

        return $prefix . $next_number;
    }

    /**
     * Insert a new quote
     *
     * @param array $data Quote data
     * @return int|false Insert ID or false on failure
     */
    public function insert(array $data)
    {
        try {
            $result = $this->wpdb->insert(
                $this->table_name,
                [
                    'quote_id' => $data['quote_id'],
                    'customer_name' => sanitize_text_field($data['customer_name']),
                    'email' => sanitize_email($data['email']),
                    'phone' => sanitize_text_field($data['phone'] ?? ''),
                    'company_name' => sanitize_text_field($data['company_name'] ?? ''),
                    'preferred_date' => sanitize_text_field($data['preferred_date'] ?? ''),
                    'preferred_time' => sanitize_text_field($data['preferred_time'] ?? ''),
                    'contract_duration' => sanitize_text_field($data['contract_duration'] ?? ''),
                    'meeting_requested' => (int) ($data['meeting_requested'] ?? 0),
                    'additional_notes' => sanitize_textarea_field($data['additional_notes'] ?? ''),
                    'cart_data' => wp_json_encode($data['cart_data']),
                    'subtotal' => (float) ($data['subtotal'] ?? 0),
                    'status' => 'pending',
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ],
                ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%f', '%s', '%s', '%s']
            );

            if ($result === false) {
                $this->logger->error('Database insert failed', [
                    'table' => $this->table_name,
                    'error' => $this->wpdb->last_error,
                ]);
                return false;
            }

            $insert_id = (int) $this->wpdb->insert_id;

            $this->log($data['quote_id'], 'created', 'Quote submitted by customer');

            return $insert_id;
        } catch (\Exception $e) {
            $this->logger->error('Exception during quote insert', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);
            return false;
        }
    }

    /**
     * Update a quote
     *
     * @param int $id Quote ID
     * @param array $data Data to update
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        try {
            $format = [];
            $update_data = [];

            foreach ($data as $key => $value) {
                switch ($key) {
                    case 'customer_name':
                    case 'email':
                    case 'phone':
                    case 'company_name':
                    case 'preferred_date':
                    case 'preferred_time':
                    case 'contract_duration':
                    case 'status':
                    case 'google_event_id':
                        $update_data[$key] = sanitize_text_field($value);
                        $format[] = '%s';
                        break;
                    case 'meeting_requested':
                    case 'calendar_synced':
                        $update_data[$key] = (int) $value;
                        $format[] = '%d';
                        break;
                    case 'admin_notes':
                    case 'additional_notes':
                        $update_data[$key] = sanitize_textarea_field($value);
                        $format[] = '%s';
                        break;
                    case 'subtotal':
                        $update_data[$key] = (float) $value;
                        $format[] = '%f';
                        break;
                }
            }

            $update_data['updated_at'] = current_time('mysql');
            $format[] = '%s';

            $result = $this->wpdb->update(
                $this->table_name,
                $update_data,
                ['id' => $id],
                $format,
                ['%d']
            );

            if ($result === false) {
                $this->logger->warning('Database update failed', [
                    'table' => $this->table_name,
                    'id' => $id,
                    'error' => $this->wpdb->last_error,
                ]);
            }

            return $result !== false;
        } catch (\Exception $e) {
            $this->logger->error('Exception during quote update', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id,
                'data' => $data,
            ]);
            return false;
        }
    }

    /**
     * Get a quote by ID
     *
     * @param int $id Quote ID
     * @return object|null
     */
    public function find(int $id): ?object
    {
        try {
            $quote = $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT * FROM {$this->table_name} WHERE id = %d",
                    $id
                )
            );

            if ($quote) {
                $quote->cart_data = json_decode($quote->cart_data, true);
            } else {
                $this->logger->warning('Quote not found', [
                    'table' => $this->table_name,
                    'id' => $id,
                ]);
            }

            return $quote;
        } catch (\Exception $e) {
            $this->logger->error('Exception during quote find', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id,
            ]);
            return null;
        }
    }

    /**
     * Get a quote by quote ID string
     *
     * @param string $quote_id Quote ID string (e.g., Q1001)
     * @return object|null
     */
    public function find_by_quote_id(string $quote_id): ?object
    {
        $quote = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE quote_id = %s",
                $quote_id
            )
        );

        if ($quote) {
            $quote->cart_data = json_decode($quote->cart_data, true);
        }

        return $quote;
    }

    /**
     * Get all quotes with pagination
     *
     * @param array $args Query arguments
     * @return array
     */
    public function get_all(array $args = []): array
    {
        $defaults = [
            'status' => '',
            'search' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'per_page' => 20,
            'page' => 1,
            'date_from' => '',
            'date_to' => '',
        ];

        $args = wp_parse_args($args, $defaults);
        $where = ['1=1'];
        $values = [];

        // Status filter
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        // Search filter
        if (!empty($args['search'])) {
            $where[] = '(quote_id LIKE %s OR customer_name LIKE %s OR email LIKE %s OR company_name LIKE %s)';
            $search_term = '%' . $this->wpdb->esc_like($args['search']) . '%';
            $values = array_merge($values, [$search_term, $search_term, $search_term, $search_term]);
        }

        // Date filters
        if (!empty($args['date_from'])) {
            $where[] = 'DATE(created_at) >= %s';
            $values[] = $args['date_from'];
        }
        if (!empty($args['date_to'])) {
            $where[] = 'DATE(created_at) <= %s';
            $values[] = $args['date_to'];
        }

        $where_clause = implode(' AND ', $where);
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']) ?? 'created_at DESC';
        $offset = ($args['page'] - 1) * $args['per_page'];

        // Build query
        $sql = "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY {$orderby} LIMIT %d OFFSET %d";
        $values[] = (int) $args['per_page'];
        $values[] = (int) $offset;

        if (!empty($values)) {
            $prepared = $this->wpdb->prepare($sql, $values);
        } else {
            $prepared = $sql;
        }

        $quotes = $this->wpdb->get_results($prepared);

        // Decode cart data
        foreach ($quotes as $quote) {
            $quote->cart_data = json_decode($quote->cart_data, true);
        }

        return $quotes;
    }

    /**
     * Get total count of quotes
     *
     * @param array $args Query arguments
     * @return int
     */
    public function get_total(array $args = []): int
    {
        $defaults = [
            'status' => '',
            'search' => '',
            'date_from' => '',
            'date_to' => '',
        ];

        $args = wp_parse_args($args, $defaults);
        $where = ['1=1'];
        $values = [];

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if (!empty($args['search'])) {
            $where[] = '(quote_id LIKE %s OR customer_name LIKE %s OR email LIKE %s OR company_name LIKE %s)';
            $search_term = '%' . $this->wpdb->esc_like($args['search']) . '%';
            $values = array_merge($values, [$search_term, $search_term, $search_term, $search_term]);
        }

        if (!empty($args['date_from'])) {
            $where[] = 'DATE(created_at) >= %s';
            $values[] = $args['date_from'];
        }
        if (!empty($args['date_to'])) {
            $where[] = 'DATE(created_at) <= %s';
            $values[] = $args['date_to'];
        }

        $where_clause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";

        if (!empty($values)) {
            return (int) $this->wpdb->get_var($this->wpdb->prepare($sql, $values));
        }

        return (int) $this->wpdb->get_var($sql);
    }

    /**
     * Delete a quote
     *
     * @param int $id Quote ID
     * @return bool
     */
    public function delete(int $id): bool
    {
        $quote = $this->find($id);
        
        if (!$quote) {
            return false;
        }

        $result = $this->wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );

        if ($result !== false) {
            $this->log($quote->quote_id, 'deleted', 'Quote deleted');
            return true;
        }

        return false;
    }

    /**
     * Update quote status
     *
     * @param int $id Quote ID
     * @param string $status New status
     * @return bool
     */
    public function update_status(int $id, string $status): bool
    {
        $valid_statuses = ['pending', 'contacted', 'closed', 'canceled'];
        
        if (!in_array($status, $valid_statuses, true)) {
            return false;
        }

        $quote = $this->find($id);
        $old_status = $quote ? $quote->status : '';

        $result = $this->update($id, ['status' => $status]);

        if ($result) {
            $this->log(
                $quote->quote_id ?? '',
                'status_changed',
                "Status changed from {$old_status} to {$status}"
            );
        }

        return $result;
    }

    /**
     * Save Google Event ID
     *
     * @param int $id Quote ID
     * @param string $event_id Google Event ID
     * @return bool
     */
    public function save_google_event(int $id, string $event_id): bool
    {
        return $this->update($id, [
            'google_event_id' => $event_id,
            'calendar_synced' => 1,
        ]);
    }

    /**
     * Log an action
     *
     * @param string $quote_id Quote ID string
     * @param string $action Action performed
     * @param string $details Action details
     * @param int|null $user_id User ID (defaults to current user)
     * @return bool
     */
    public function log(string $quote_id, string $action, string $details, ?int $user_id = null): bool
    {
        $result = $this->wpdb->insert(
            $this->logs_table,
            [
                'quote_id' => $quote_id,
                'action' => $action,
                'details' => $details,
                'user_id' => $user_id ?? get_current_user_id(),
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%d', '%s']
        );

        return $result !== false;
    }

    /**
     * Get logs for a quote
     *
     * @param string $quote_id Quote ID string
     * @return array
     */
    public function get_logs(string $quote_id): array
    {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->logs_table} WHERE quote_id = %s ORDER BY created_at DESC",
                $quote_id
            )
        );
    }

    /**
     * Get statistics
     *
     * @return array
     */
    public function get_statistics(): array
    {
        return [
            'total' => (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}"),
            'pending' => (int) $this->wpdb->get_var(
                $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s", 'pending')
            ),
            'contacted' => (int) $this->wpdb->get_var(
                $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s", 'contacted')
            ),
            'closed' => (int) $this->wpdb->get_var(
                $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s", 'closed')
            ),
            'canceled' => (int) $this->wpdb->get_var(
                $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s", 'canceled')
            ),
            'meetings_requested' => (int) $this->wpdb->get_var(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE meeting_requested = 1"
            ),
            'meetings_scheduled' => (int) $this->wpdb->get_var(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE calendar_synced = 1"
            ),
        ];
    }

    /**
     * Export quotes to CSV
     *
     * @param array $args Query arguments
     * @return string CSV content
     */
    public function export_csv(array $args = []): string
    {
        $args['per_page'] = 9999;
        $quotes = $this->get_all($args);

        $csv = [];
        
        // Headers
        $csv[] = [
            'Quote ID',
            'Customer Name',
            'Email',
            'Phone',
            'Company',
            'Preferred Date',
            'Preferred Time',
            'Contract Duration',
            'Meeting Requested',
            'Subtotal',
            'Status',
            'Created At',
            'Google Event ID',
        ];

        // Data rows
        foreach ($quotes as $quote) {
            $csv[] = [
                $quote->quote_id,
                $quote->customer_name,
                $quote->email,
                $quote->phone,
                $quote->company_name,
                $quote->preferred_date,
                $quote->preferred_time,
                $quote->contract_duration,
                $quote->meeting_requested ? 'Yes' : 'No',
                $quote->subtotal,
                $quote->status,
                $quote->created_at,
                $quote->google_event_id,
            ];
        }

        // Convert to CSV string
        $output = '';
        foreach ($csv as $row) {
            $output .= implode(',', array_map(function ($field) {
                return '"' . str_replace('"', '""', $field ?? '') . '"';
            }, $row)) . "\n";
        }

        return $output;
    }
}
