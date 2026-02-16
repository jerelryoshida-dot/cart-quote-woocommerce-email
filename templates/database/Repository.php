<?php
/**
 * Repository Class
 *
 * Implements the Repository pattern for database operations.
 * Provides a clean abstraction layer between business logic and database.
 *
 * FEATURES:
 * - Type-safe CRUD operations
 * - Prepared statements (SQL injection prevention)
 * - Pagination support
 * - Search and filtering
 * - Error logging
 *
 * NAMING CONVENTION:
 * - find() - Get single record
 * - get_all() - Get multiple records
 * - insert() - Create record
 * - update() - Update record
 * - delete() - Remove record
 *
 * @package PLUGIN_NAMESPACE\Database
 * @author YOUR_NAME
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PLUGIN_NAMESPACE\Database;

use PLUGIN_NAMESPACE\Core\Debug_Logger;

/**
 * Class Repository
 */
class Repository
{
    /**
     * WordPress database object
     *
     * @var \wpdb
     */
    private \wpdb $wpdb;

    /**
     * Main table name (with prefix)
     *
     * @var string
     */
    private string $table_name;

    /**
     * Logs table name (with prefix)
     *
     * @var string
     */
    private string $logs_table;

    /**
     * Debug logger instance
     *
     * @var Debug_Logger
     */
    private Debug_Logger $logger;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . PLUGIN_PREFIX_TABLE_MAIN;
        $this->logs_table = $wpdb->prefix . 'plugin_slug_logs';
        $this->logger = Debug_Logger::get_instance();
    }

    /**
     * ============================================================================
     * CREATE OPERATIONS
     * ============================================================================
     */

    /**
     * Insert a new record
     *
     * @param array<string, mixed> $data Record data
     * @return int|false Insert ID on success, false on failure
     */
    public function insert(array $data)
    {
        try {
            // Prepare data with sanitization
            $insert_data = [
                'title'      => sanitize_text_field($data['title'] ?? ''),
                'content'    => sanitize_textarea_field($data['content'] ?? ''),
                'status'     => sanitize_text_field($data['status'] ?? 'draft'),
                'author_id'  => (int) ($data['author_id'] ?? get_current_user_id()),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ];

            // Format specifiers for wpdb::insert
            $format = ['%s', '%s', '%s', '%d', '%s', '%s'];

            // Execute insert
            $result = $this->wpdb->insert($this->table_name, $insert_data, $format);

            if ($result === false) {
                $this->logger->error('Database insert failed', [
                    'table' => $this->table_name,
                    'error' => $this->wpdb->last_error,
                    'data'  => $insert_data,
                ]);
                return false;
            }

            $insert_id = (int) $this->wpdb->insert_id;

            // Log the action
            $this->log($insert_id, 'created', 'Record created');

            return $insert_id;

        } catch (\Exception $e) {
            $this->logger->error('Exception during insert', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * ============================================================================
     * READ OPERATIONS
     * ============================================================================
     */

    /**
     * Find a single record by ID
     *
     * @param int $id Record ID
     * @return object|null Record object or null if not found
     */
    public function find(int $id): ?object
    {
        try {
            // Always use prepare() for safety
            $record = $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT * FROM {$this->table_name} WHERE id = %d",
                    $id
                )
            );

            if (!$record) {
                $this->logger->debug('Record not found', ['id' => $id]);
                return null;
            }

            return $record;

        } catch (\Exception $e) {
            $this->logger->error('Exception during find', [
                'id'        => $id,
                'exception' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get all records with pagination and filtering
     *
     * @param array<string, mixed> $args Query arguments
     * @return array<int, object> Array of record objects
     */
    public function get_all(array $args = []): array
    {
        // Default arguments
        $defaults = [
            'status'    => '',
            'search'    => '',
            'author_id' => 0,
            'orderby'   => 'created_at',
            'order'     => 'DESC',
            'per_page'  => 20,
            'page'      => 1,
            'date_from' => '',
            'date_to'   => '',
        ];

        $args = wp_parse_args($args, $defaults);

        // Build WHERE clause
        $where = ['1=1'];
        $values = [];

        // Status filter
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = sanitize_text_field($args['status']);
        }

        // Author filter
        if (!empty($args['author_id'])) {
            $where[] = 'author_id = %d';
            $values[] = (int) $args['author_id'];
        }

        // Search filter
        if (!empty($args['search'])) {
            $where[] = '(title LIKE %s OR content LIKE %s)';
            $search_term = '%' . $this->wpdb->esc_like($args['search']) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
        }

        // Date filters
        if (!empty($args['date_from'])) {
            $where[] = 'DATE(created_at) >= %s';
            $values[] = sanitize_text_field($args['date_from']);
        }
        if (!empty($args['date_to'])) {
            $where[] = 'DATE(created_at) <= %s';
            $values[] = sanitize_text_field($args['date_to']);
        }

        // Build ORDER BY (validate to prevent SQL injection)
        $orderby = $this->validate_orderby($args['orderby']);
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Calculate offset
        $per_page = max(1, (int) $args['per_page']);
        $page = max(1, (int) $args['page']);
        $offset = ($page - 1) * $per_page;

        // Build complete query
        $where_clause = implode(' AND ', $where);
        $sql = "SELECT * FROM {$this->table_name} 
                WHERE {$where_clause} 
                ORDER BY {$orderby} {$order} 
                LIMIT %d OFFSET %d";

        // Add limit/offset to values
        $values[] = $per_page;
        $values[] = $offset;

        // Prepare and execute
        $prepared = $this->wpdb->prepare($sql, $values);
        $results = $this->wpdb->get_results($prepared);

        return $results ?: [];
    }

    /**
     * Get total count of records
     *
     * @param array<string, mixed> $args Query arguments (same as get_all, minus pagination)
     * @return int Total count
     */
    public function get_total(array $args = []): int
    {
        $defaults = [
            'status'    => '',
            'search'    => '',
            'author_id' => 0,
            'date_from' => '',
            'date_to'   => '',
        ];

        $args = wp_parse_args($args, $defaults);

        // Build WHERE clause (same as get_all)
        $where = ['1=1'];
        $values = [];

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = sanitize_text_field($args['status']);
        }

        if (!empty($args['author_id'])) {
            $where[] = 'author_id = %d';
            $values[] = (int) $args['author_id'];
        }

        if (!empty($args['search'])) {
            $where[] = '(title LIKE %s OR content LIKE %s)';
            $search_term = '%' . $this->wpdb->esc_like($args['search']) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
        }

        if (!empty($args['date_from'])) {
            $where[] = 'DATE(created_at) >= %s';
            $values[] = sanitize_text_field($args['date_from']);
        }
        if (!empty($args['date_to'])) {
            $where[] = 'DATE(created_at) <= %s';
            $values[] = sanitize_text_field($args['date_to']);
        }

        $where_clause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";

        if (!empty($values)) {
            return (int) $this->wpdb->get_var($this->wpdb->prepare($sql, $values));
        }

        return (int) $this->wpdb->get_var($sql);
    }

    /**
     * ============================================================================
     * UPDATE OPERATIONS
     * ============================================================================
     */

    /**
     * Update a record
     *
     * @param int $id Record ID
     * @param array<string, mixed> $data Data to update
     * @return bool True on success, false on failure
     */
    public function update(int $id, array $data): bool
    {
        try {
            $update_data = [];
            $format = [];

            // Build update data with sanitization
            foreach ($data as $key => $value) {
                switch ($key) {
                    case 'title':
                        $update_data['title'] = sanitize_text_field($value);
                        $format[] = '%s';
                        break;
                    case 'content':
                        $update_data['content'] = sanitize_textarea_field($value);
                        $format[] = '%s';
                        break;
                    case 'status':
                        $update_data['status'] = sanitize_text_field($value);
                        $format[] = '%s';
                        break;
                }
            }

            // Always update the updated_at timestamp
            $update_data['updated_at'] = current_time('mysql');
            $format[] = '%s';

            // Execute update
            $result = $this->wpdb->update(
                $this->table_name,
                $update_data,
                ['id' => $id],
                $format,
                ['%d']
            );

            if ($result === false) {
                $this->logger->error('Database update failed', [
                    'table' => $this->table_name,
                    'id'    => $id,
                    'error' => $this->wpdb->last_error,
                ]);
                return false;
            }

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Exception during update', [
                'id'        => $id,
                'exception' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Update record status
     *
     * @param int $id Record ID
     * @param string $status New status
     * @return bool
     */
    public function update_status(int $id, string $status): bool
    {
        // Validate status against allowed values
        $valid_statuses = ['draft', 'published', 'archived'];
        
        if (!in_array($status, $valid_statuses, true)) {
            $this->logger->warning('Invalid status value', [
                'status' => $status,
                'valid'  => $valid_statuses,
            ]);
            return false;
        }

        $result = $this->update($id, ['status' => $status]);

        if ($result) {
            $record = $this->find($id);
            $this->log($id, 'status_changed', "Status changed to {$status}");
        }

        return $result;
    }

    /**
     * ============================================================================
     * DELETE OPERATIONS
     * ============================================================================
     */

    /**
     * Delete a record
     *
     * @param int $id Record ID
     * @return bool True on success, false on failure
     */
    public function delete(int $id): bool
    {
        // Get record before deleting (for logging)
        $record = $this->find($id);
        
        if (!$record) {
            return false;
        }

        $result = $this->wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );

        if ($result !== false) {
            $this->log($id, 'deleted', 'Record deleted');
            return true;
        }

        return false;
    }

    /**
     * ============================================================================
     * LOGGING
     * ============================================================================
     */

    /**
     * Log an action for a record
     *
     * @param int $record_id Record ID
     * @param string $action Action performed
     * @param string $details Action details
     * @return bool
     */
    public function log(int $record_id, string $action, string $details): bool
    {
        $result = $this->wpdb->insert(
            $this->logs_table,
            [
                'item_id'    => $record_id,
                'action'     => sanitize_text_field($action),
                'details'    => sanitize_textarea_field($details),
                'user_id'    => get_current_user_id(),
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%d', '%s']
        );

        return $result !== false;
    }

    /**
     * Get logs for a record
     *
     * @param int $record_id Record ID
     * @return array<int, object>
     */
    public function get_logs(int $record_id): array
    {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->logs_table} 
                 WHERE item_id = %d 
                 ORDER BY created_at DESC",
                $record_id
            )
        ) ?: [];
    }

    /**
     * ============================================================================
     * STATISTICS
     * ============================================================================
     */

    /**
     * Get statistics
     *
     * @return array<string, int>
     */
    public function get_statistics(): array
    {
        $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as drafts,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived
        FROM {$this->table_name}";

        $result = $this->wpdb->get_row($sql);

        return [
            'total'     => (int) ($result->total ?? 0),
            'drafts'    => (int) ($result->drafts ?? 0),
            'published' => (int) ($result->published ?? 0),
            'archived'  => (int) ($result->archived ?? 0),
        ];
    }

    /**
     * ============================================================================
     * EXPORT
     * ============================================================================
     */

    /**
     * Export records to CSV
     *
     * Uses chunking for memory efficiency with large datasets.
     *
     * @param array<string, mixed> $args Query arguments
     * @return string CSV content
     */
    public function export_csv(array $args = []): string
    {
        $args['per_page'] = 500; // Process in chunks
        $page = 1;
        $csv = [];

        // CSV headers
        $csv[] = ['ID', 'Title', 'Status', 'Author ID', 'Created At'];

        do {
            $args['page'] = $page;
            $records = $this->get_all($args);

            foreach ($records as $record) {
                $csv[] = [
                    $record->id,
                    $record->title,
                    $record->status,
                    $record->author_id,
                    $record->created_at,
                ];
            }

            $page++;

        } while (count($records) === $args['per_page']);

        // Convert to CSV string
        $output = '';
        foreach ($csv as $row) {
            $output .= implode(',', array_map(function ($field) {
                return '"' . str_replace('"', '""', $field ?? '') . '"';
            }, $row)) . "\n";
        }

        return $output;
    }

    /**
     * ============================================================================
     * HELPERS
     * ============================================================================
     */

    /**
     * Validate orderby column
     *
     * Prevents SQL injection by whitelisting allowed columns.
     *
     * @param string $orderby Column name
     * @return string Validated column name
     */
    private function validate_orderby(string $orderby): string
    {
        $allowed = [
            'id'         => 'id',
            'title'      => 'title',
            'status'     => 'status',
            'author_id'  => 'author_id',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];

        return $allowed[$orderby] ?? 'created_at';
    }
}
