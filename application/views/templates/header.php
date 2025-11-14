<!DOCTYPE html>
<html lang="<?php echo $this->config->item('language') == 'russian' ? 'ru' : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line('app_name'); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">

    <!-- jQuery - Load early so page scripts can use it -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link:hover {
            background: #495057;
        }
        .sidebar .nav-link.active {
            background: #007bff;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-online {
            background: #28a745;
            color: #fff;
        }
        .status-offline {
            background: #dc3545;
            color: #fff;
        }
        .status-running {
            background: #28a745;
            color: #fff;
        }
        .status-stopped {
            background: #6c757d;
            color: #fff;
        }
        .status-paused {
            background: #ffc107;
            color: #000;
        }
        .stat-card {
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar p-0">
                <div class="sidebar-sticky">
                    <div class="p-3 bg-dark">
                        <h4 class="text-white"><?php echo $this->lang->line('app_title'); ?></h4>
                    </div>
                    <ul class="nav flex-column p-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $this->uri->segment(1) == 'dashboard' || $this->uri->segment(1) == '' ? 'active' : ''; ?>" href="<?php echo base_url(); ?>">
                                <i class="fas fa-tachometer-alt"></i> <?php echo $this->lang->line('nav_dashboard'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $this->uri->segment(1) == 'campaigns' ? 'active' : ''; ?>" href="<?php echo base_url('campaigns'); ?>">
                                <i class="fas fa-bullhorn"></i> <?php echo $this->lang->line('nav_campaigns'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $this->uri->segment(1) == 'cdr' ? 'active' : ''; ?>" href="<?php echo base_url('cdr'); ?>">
                                <i class="fas fa-phone-volume"></i> <?php echo $this->lang->line('nav_call_records'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $this->uri->segment(1) == 'monitoring' ? 'active' : ''; ?>" href="<?php echo base_url('monitoring'); ?>">
                                <i class="fas fa-chart-line"></i> <?php echo $this->lang->line('nav_monitoring'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $this->uri->segment(1) == 'ivr' ? 'active' : ''; ?>" href="<?php echo base_url('ivr'); ?>">
                                <i class="fas fa-phone-square"></i> <?php echo $this->lang->line('nav_ivr_menus'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $this->uri->segment(1) == 'settings' ? 'active' : ''; ?>" href="<?php echo base_url('settings'); ?>">
                                <i class="fas fa-cog"></i> <?php echo $this->lang->line('nav_settings'); ?>
                            </a>
                        </li>
                        <li class="nav-item mt-3 border-top pt-3">
                            <div class="px-3 text-white-50 small">
                                <i class="fas fa-user-circle"></i> <?php echo $this->auth->user()->full_name; ?>
                                <?php if ($this->auth->is_admin()): ?>
                                    <span class="badge badge-warning ml-1"><?php echo $this->lang->line('nav_admin'); ?></span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('login/logout'); ?>" onclick="return confirm('<?php echo $this->lang->line('nav_confirm_logout'); ?>');">
                                <i class="fas fa-sign-out-alt"></i> <?php echo $this->lang->line('nav_logout'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main role="main" class="col-md-10 ml-sm-auto px-4 py-4">
                <!-- Language Switcher -->
                <div class="position-fixed" style="top: 20px; right: 20px; z-index: 1000;">
                    <div class="btn-group">
                        <?php $current_lang = $this->config->item('language'); ?>
                        <a href="<?php echo base_url('language/switch_lang/english'); ?>"
                           class="btn btn-sm <?php echo $current_lang == 'english' ? 'btn-primary' : 'btn-outline-primary'; ?>"
                           title="English">
                            EN
                        </a>
                        <a href="<?php echo base_url('language/switch_lang/russian'); ?>"
                           class="btn btn-sm <?php echo $current_lang == 'russian' ? 'btn-primary' : 'btn-outline-primary'; ?>"
                           title="Русский">
                            RU
                        </a>
                    </div>
                </div>
