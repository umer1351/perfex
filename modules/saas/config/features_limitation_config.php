<?php

$config['limitations'] = [
    'per_plan_invoices'            => ['label' => 'Invoices', 'dbTable' => 'invoices', 'hookName' => 'before_invoice_added'],
    'per_plan_customers'           => ['label' => 'Customers', 'dbTable' => 'clients', 'hookName' => 'before_client_added'],
    'per_plan_contracts'           => ['label' => 'Contracts', 'dbTable' => 'contracts', 'hookName' => 'before_contract_added'],
    'per_plan_projects'            => ['label' => 'Projects', 'dbTable' => 'projects', 'hookName' => 'before_add_project'],
    'per_plan_estimates'           => ['label' => 'Estimates', 'dbTable' => 'estimates', 'hookName' => 'before_estimate_added'],
    'per_plan_credit_notes'        => ['label' => 'Credit Notes', 'dbTable' => 'creditnotes', 'hookName' => 'before_create_credit_note'],
    'per_plan_payments'            => ['label' => 'Payments', 'dbTable' => 'invoicepaymentrecords', 'hookName' => 'before_payment_recorded'],
    'per_plan_items'               => ['label' => 'Items', 'dbTable' => 'items', 'hookName' => 'before_item_created'],
    'per_plan_proposals'           => ['label' => 'Proposals', 'dbTable' => 'proposals', 'hookName' => 'before_create_proposal'],
    'per_plan_expenses'            => ['label' => 'Expenses', 'dbTable' => 'expenses', 'hookName' => 'before_expense_added'],
    'per_plan_tasks'               => ['label' => 'Tasks', 'dbTable' => 'tasks', 'hookName' => 'before_add_task'],
    'per_plan_support_tickets'     => ['label' => 'Support Tickets', 'dbTable' => 'tickets', 'hookName' => 'before_ticket_created'],
    'per_plan_leads'               => ['label' => 'Leads', 'dbTable' => 'leads', 'hookName' => 'before_lead_added'],
    'per_plan_staff'               => ['label' => 'Staffs', 'dbTable' => 'staff', 'hookName' => 'before_create_staff_member'],
];
