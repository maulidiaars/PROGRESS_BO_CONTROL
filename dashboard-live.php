<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 LIVE DASHBOARD - BO CONTROL MONITORING REAL-TIME</title>
    
    <!-- FAVICON -->
    <link href="assets/img/favicon.png" rel="icon">
    
    <!-- BOOTSTRAP 5 -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- BOOTSTRAP ICONS -->
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    
    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- GOOGLE FONTS -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- APEXCHARTS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.css">
    
    <style>
        /* ========== GLOBAL STYLES ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            font-family: 'Roboto', 'Inter', Arial, sans-serif;
            background: linear-gradient(135deg, #0f3460 0%, #1a1a2e 100%);
        }
        
        /* ========== HEADER ========== */
        .dashboard-header {
            background: rgba(15, 52, 96, 0.95);
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            border-bottom: 3px solid #00adb5;
            backdrop-filter: blur(10px);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-container img {
            height: 45px;
            width: auto;
        }

        .header-title {
            display: flex;
            flex-direction: column;
        }

        .main-title {
            font-family: 'Inter', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.3px;
            line-height: 1.2;
        }

        .sub-title {
            font-size: 14px;
            color: #00adb5;
            font-weight: 600;
            margin-top: 2px;
        }

        /* BACK BUTTON */
        .back-container {
            display: flex;
            align-items: center;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(0, 173, 181, 0.5);
            border-radius: 6px;
            padding: 8px 15px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .back-btn:hover {
            background: rgba(0, 173, 181, 0.2);
            transform: translateX(-3px);
            color: white;
            text-decoration: none;
        }

        /* ========== DATE TIME DISPLAY ========== */
        .datetime-display {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            color: #ffffff;
        }

        .date-display {
            font-size: 16px;
            font-weight: 500;
            color: #a9b7c6;
        }

        .time-display {
            font-family: 'Inter', monospace;
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.8px;
            line-height: 1.2;
            margin-top: 2px;
        }

        /* ========== MAIN CONTAINER ========== */
        .dashboard-container {
            display: flex;
            height: calc(100vh - 80px);
            margin-top: 80px;
            padding: 20px;
            gap: 20px;
        }
        
        /* ========== LEFT PANEL - REAL-TIME CHARTS ========== */
        .live-charts-container {
            width: 35%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .live-gauge-card, .hourly-card, .info-card {
            background: rgba(22, 33, 62, 0.9);
            border-radius: 12px;
            padding: 15px 20px 20px 20px;
            border: 1px solid rgba(0, 173, 181, 0.3);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        .gauge-container {
            text-align: center;
            padding-top: 20px; /* 🔽 turunin chart */
        }

        #todayGauge {
            height: 160px !important;
            margin: 0 auto;
        }

        
        .gauge-header, .hourly-header, .info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(0, 173, 181, 0.2);
        }
        
        .gauge-header h6, .hourly-header h6, .info-header h6 {
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        #todayGauge { height: 50px !important; margin-top: -10px !important; }

        
        .live-time {
            font-size: 12px;
            color: #00adb5;
            font-family: monospace;
        }
        
        .shift-badge {
            background: linear-gradient(135deg, #00adb5 0%, #00838f 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
    
        
        .gauge-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stat {
            text-align: center;
        }
        
        .stat .label {
            display: block;
            font-size: 11px;
            color: #a9b7c6;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .stat .value {
            font-size: 18px;
            font-weight: 800;
            display: block;
            color: #ffffff;
        }
        
        .stat .value.text-success {
            color: #2ecc71;
        }
        
        .stat .value.text-primary {
            color: #3498db;
        }
        
        .stat .value.text-warning {
            color: #f39c12;
        }
        
        .stat .value.text-danger {
            color: #e74c3c;
        }
        
        /* ========== INFORMATION SECTION - PERBAIKAN ========== */
        .info-card {
          max-height: 300px; /* UBAH DARI 300px KE 400px */
          min-height: 250px; /* TAMBAHKAN MIN-HEIGHT */
            display: flex;
            flex-direction: column;
        }
        
        .info-list {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #00adb5 #0f3460;
        }
        
        .info-list::-webkit-scrollbar {
            width: 6px;
        }
        
        .info-list::-webkit-scrollbar-track {
            background: #0f3460;
            border-radius: 3px;
        }
        
        .info-list::-webkit-scrollbar-thumb {
            background: #00adb5;
            border-radius: 3px;
        }
        
        .info-list::-webkit-scrollbar-thumb:hover {
            background: #00838f;
        }
        
        /* ========== INFORMATION SECTION - PERBAIKAN FORMAT ========== */
        .info-item {
            background: rgba(0, 173, 181, 0.1);
            border-left: 4px solid #00adb5;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            cursor: default;
        }

        .info-item.urgent {
            border-left-color: #ff416c;
            background: rgba(255, 65, 108, 0.1);
        }

        .info-item.assigned {
            border-left-color: #ffa726;
            background: rgba(255, 167, 38, 0.1);
        }

        .info-content {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .info-icon {
            width: 24px;
            height: 24px;
            min-width: 24px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            background: rgba(0, 173, 181, 0.2);
            color: #00adb5;
        }

        .info-item.urgent .info-icon {
            background: rgba(255, 65, 108, 0.2);
            color: #ff416c;
        }

        .info-item.assigned .info-icon {
            background: rgba(255, 167, 38, 0.2);
            color: #ffa726;
        }

        .info-details {
            flex: 1;
            min-width: 0;
        }

        .info-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .info-title span:first-child {
            font-size: 12px;
            font-weight: bold;
            color: #ffffff;
        }

        .info-status {
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 3px;
            text-transform: uppercase;
        }

        .info-message {
            color: #d1d9e6;
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 4px;
            word-break: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .info-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #a9b7c6;
        }

        .info-time {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .info-from {
            color: #888;
        }
        
        .info-item:hover {
            background: rgba(0, 173, 181, 0.2);
            transform: translateX(3px);
        }
        
        .info-item.urgent {
            border-left-color: #ff416c;
            background: rgba(255, 65, 108, 0.1);
            animation: urgentPulse 2s infinite;
        }
        
        .info-item.assigned {
            border-left-color: #ffa726;
            background: rgba(255, 167, 38, 0.1);
        }
        
        .info-item.unread::after {
            content: '';
            position: absolute;
            top: 12px;
            right: 12px;
            width: 8px;
            height: 8px;
            background: #ff416c;
            border-radius: 50%;
            animation: blinkDot 1.5s infinite;
        }
        
        @keyframes urgentPulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 65, 108, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 65, 108, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 65, 108, 0); }
        }
        
        @keyframes blinkDot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        .info-content {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .info-icon {
            width: 24px;
            height: 24px;
            min-width: 24px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            background: rgba(0, 173, 181, 0.2);
            color: #00adb5;
        }
        
        .info-item.urgent .info-icon {
            background: rgba(255, 65, 108, 0.2);
            color: #ff416c;
        }
        
        .info-item.assigned .info-icon {
            background: rgba(255, 167, 38, 0.2);
            color: #ffa726;
        }
        
        .info-details {
            flex: 1;
            min-width: 0; /* Untuk ellipsis */
        }
        
        .info-title {
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 3px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .info-message {
            color: #d1d9e6;
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 4px;
            word-break: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .info-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
        }
        
        .info-time {
            color: #a9b7c6;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .info-status {
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 3px;
            text-transform: uppercase;
        }
        
        .info-status.bg-danger { background: #e74c3c; color: white; }
        .info-status.bg-warning { background: #f39c12; color: white; }
        .info-status.bg-primary { background: #3498db; color: white; }
        .info-status.bg-success { background: #2ecc71; color: white; }
        .info-status.bg-secondary { background: #95a5a6; color: white; }
        
        /* ========== RIGHT PANEL - DATA TABLE ========== */
        .main-data-panel {
            width: 65%;
            background: rgba(22, 33, 62, 0.9);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0, 173, 181, 0.3);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 173, 181, 0.2);
        }
        
        .panel-title {
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .panel-title i {
            color: #00adb5;
            font-size: 22px;
        }
        
        .live-badge {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 6px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 65, 108, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 65, 108, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 65, 108, 0); }
        }
        
        .live-dot {
            width: 8px;
            height: 8px;
            background: #fff;
            border-radius: 50%;
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        .panel-stats {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 12px;
            background: rgba(15, 52, 96, 0.5);
            border-radius: 8px;
            border: 1px solid rgba(0, 173, 181, 0.3);
            min-width: 80px;
        }
        
        .stat-label {
            color: #a9b7c6;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            white-space: nowrap;
        }
        
        .stat-value {
            color: #ffffff;
            font-size: 16px;
            font-weight: 800;
            margin-top: 4px;
        }
        
        /* ========== NEW DATA TABLE DESIGN ========== */
        .table-wrapper {
            flex: 1;
            overflow: hidden;
            border-radius: 8px;
            position: relative;
        }
        
        .table-container {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .table-fixed-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: rgba(15, 52, 96, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #00adb5;
        }
        
        .table-fixed-header table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            table-layout: fixed;
        }
        
        .table-fixed-header th {
            padding: 14px 8px;
            text-align: left;
            color: #ffffff;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .table-fixed-header th i {
            margin-right: 5px;
            color: #00adb5;
            font-size: 10px;
        }
        
        /* SET WIDTH UNTUK SETIAP KOLOM */
        .table-fixed-header th:nth-child(1) { width: 70px; }  /* CODE */
        .table-fixed-header th:nth-child(2) { width: 160px; } /* SUPPLIER */
        .table-fixed-header th:nth-child(3) { width: 60px; }  /* PIC */
        .table-fixed-header th:nth-child(4) { width: 90px; }  /* DAY SHIFT */
        .table-fixed-header th:nth-child(5) { width: 90px; }  /* NIGHT SHIFT */
        .table-fixed-header th:nth-child(6) { width: 85px; }  /* ORDER */
        .table-fixed-header th:nth-child(7) { width: 85px; }  /* INCOMING */
        .table-fixed-header th:nth-child(8) { width: 85px; }  /* REMAIN */
        .table-fixed-header th:nth-child(9) { width: 80px; }  /* COMPLETION */
        .table-fixed-header th:nth-child(10) { width: 80px; } /* STATUS */
        
        .table-scroll-body {
            position: absolute;
            top: 48px; /* Height of header */
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
        }
        
        .scrolling-content {
            width: 100%;
            position: relative;
        }
        
        .scrolling-content table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
            table-layout: fixed;
        }
        
        .scrolling-content td {
            padding: 12px 6px;
            text-align: left;
            border-bottom: 1px solid rgba(15, 52, 96, 0.3);
            color: #e4e6eb;
            font-size: 11px;
            font-weight: 400;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }
        
        /* SET WIDTH YANG SAMA DENGAN HEADER */
        .scrolling-content td:nth-child(1) { width: 70px; }
        .scrolling-content td:nth-child(2) { width: 160px; }
        .scrolling-content td:nth-child(3) { width: 60px; }
        .scrolling-content td:nth-child(4) { width: 90px; }
        .scrolling-content td:nth-child(5) { width: 90px; }
        .scrolling-content td:nth-child(6) { width: 85px; }
        .scrolling-content td:nth-child(7) { width: 85px; }
        .scrolling-content td:nth-child(8) { width: 85px; }
        .scrolling-content td:nth-child(9) { width: 80px; }
        .scrolling-content td:nth-child(10) { width: 80px; }
        
        .scrolling-content tr {
            background: transparent;
            transition: all 0.2s ease;
        }
        
        .scrolling-content tr:hover {
            background: rgba(0, 173, 181, 0.08);
        }
        
        .scrolling-content tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.02);
        }
        
        /* ========== NEW COLUMN STYLES ========== */
        .supplier-code {
            color: #ffffff;
            font-weight: 700;
            font-size: 11px;
            background: rgba(0, 173, 181, 0.1);
            padding: 4px 6px;
            border-radius: 4px;
            border: 1px solid rgba(0, 173, 181, 0.3);
            display: inline-block;
            text-align: center;
            width: 100%;
        }
        
        .supplier-name {
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 500;
            color: #d1d9e6;
            font-size: 11px;
        }
        
        .pic-badge {
            background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
            color: white;
            padding: 3px 5px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            display: inline-block;
            text-align: center;
            width: 100%;
        }
        
        .progress-cell {
            width: 90px;
        }
        
        .progress-container {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .progress-info {
            display: flex;
            flex-direction: column;
            gap: 3px;
            width: 100%;
        }
        
        .progress-label {
            font-size: 8px;
            color: #a9b7c6;
            text-transform: uppercase;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
        }
        
        .progress-label span {
            color: #ffffff;
            font-size: 8px;
        }
        
        .progress-bar-horizontal {
            width: 100%;
            height: 5px;
            background: rgba(15, 52, 96, 0.5);
            border-radius: 2px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.8s ease;
        }
        
        .progress-fill.ds {
            background: linear-gradient(90deg, #3498db 0%, #2980b9 100%);
        }
        
        .progress-fill.ns {
            background: linear-gradient(90deg, #e74c3c 0%, #c0392b 100%);
        }
        
        .quantity-cell {
            width: 85px;
        }
        
        .quantity-display {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 5px 6px;
            border-radius: 5px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            width: 100%;
        }
        
        .quantity-value {
            font-weight: 800;
            font-size: 12px;
            color: #ffffff;
            margin-bottom: 1px;
        }
        
        .quantity-label {
            font-size: 8px;
            color: #a9b7c6;
            text-transform: uppercase;
        }
        
        .quantity-good {
            border-color: rgba(46, 204, 113, 0.4);
            background: rgba(46, 204, 113, 0.1);
        }
        
        .quantity-good .quantity-value {
            color: #2ecc71;
        }
        
        .quantity-warning {
            border-color: rgba(241, 196, 15, 0.4);
            background: rgba(241, 196, 15, 0.1);
        }
        
        .quantity-warning .quantity-value {
            color: #f1c40f;
        }
        
        .quantity-danger {
            border-color: rgba(231, 76, 60, 0.4);
            background: rgba(231, 76, 60, 0.1);
        }
        
        .quantity-danger .quantity-value {
            color: #e74c3c;
        }
        
        .status-badge {
            padding: 5px 6px;
            border-radius: 5px;
            font-size: 9px;
            font-weight: 700;
            display: inline-block;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            width: 100%;
        }
        
        .status-ok {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
        }
        
        .status-on-progress {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .status-delay {
            background: linear-gradient(135deg, #f39c12 0%, #d68910 100%);
            color: white;
        }
        
        .status-over {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        
        .rate-display {
            font-weight: 700;
            font-size: 12px;
            color: #ffffff;
            text-align: center;
            display: block;
        }
        
        .rate-good {
            color: #2ecc71;
        }
        
        .rate-warning {
            color: #f1c40f;
        }
        
        .rate-danger {
            color: #e74c3c;
        }
        
        /* ========== AUTO SCROLL CONTROLS ========== */
        .scroll-controls {
            position: absolute;
            bottom: 10px;
            right: 10px;
            z-index: 200;
            display: flex;
            gap: 8px;
        }
        
        .scroll-btn {
            background: rgba(0, 173, 181, 0.8);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .scroll-btn:hover {
            background: rgba(0, 173, 181, 1);
            transform: translateY(-1px);
        }
        
        .scroll-btn.active {
            background: rgba(255, 167, 38, 0.8);
        }
        
        /* ========== CONTROL BUTTONS ========== */
        .control-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .control-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
        }
        
        .control-btn-primary {
            background: linear-gradient(135deg, #00adb5 0%, #00838f 100%);
            color: white;
        }
        
        .control-btn-primary:hover {
            background: linear-gradient(135deg, #00838f 0%, #006064 100%);
            transform: translateY(-2px);
        }
        
        .control-btn-secondary {
            background: linear-gradient(135deg, #0f3460 0%, #1a1a2e 100%);
            color: #a9b7c6;
            border: 1px solid #00adb5;
        }
        
        .control-btn-secondary:hover {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
            transform: translateY(-2px);
        }
        
        /* ========== LOADING SPINNER ========== */
        .loading-spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* ========== HOME BUTTON ========== */
        .home-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #00adb5 0%, #00838f 100%);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 4px 12px rgba(0, 173, 181, 0.4);
            z-index: 1001;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .home-button:hover {
            background: linear-gradient(135deg, #00838f 0%, #006064 100%);
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 173, 181, 0.5);
            color: white;
        }
        
        /* ========== EMPTY STATE ========== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #a9b7c6;
        }
        
        .empty-state i {
            font-size: 40px;
            margin-bottom: 10px;
            opacity: 0.3;
        }
        
        .empty-state p {
            font-size: 14px;
            font-weight: 500;
        }
        
        /* ========== ALERT BADGE ========== */
        .alert-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff416c;
            color: white;
            font-size: 10px;
            font-weight: 700;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* ========== RESPONSIVE ========== */
        @media (max-width: 1200px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .live-charts-container,
            .main-data-panel {
                width: 100%;
            }
            
            .main-data-panel {
                height: 60vh;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                padding: 0 15px;
                height: auto;
                min-height: 70px;
                flex-wrap: wrap;
            }
            
            .logo-container {
                width: 100%;
                justify-content: center;
                margin-bottom: 5px;
            }
            
            .back-container {
                order: 2;
                margin: 10px 0;
                justify-content: center;
            }
            
            .datetime-display {
                order: 3;
                width: 100%;
                text-align: center;
                margin-top: 5px;
            }
            
            .main-title {
                font-size: 18px;
                text-align: center;
            }
            
            .sub-title {
                font-size: 12px;
                text-align: center;
            }
            
            .date-display {
                font-size: 14px;
            }
            
            .time-display {
                font-size: 22px;
            }
            
            .dashboard-container {
                margin-top: 120px;
                padding: 10px;
                gap: 10px;
            }
            
            .panel-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .panel-stats {
                width: 100%;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 5px;
            }
            
            .stat-item {
                flex: 1;
                min-width: calc(50% - 5px);
                padding: 6px 8px;
                margin-bottom: 5px;
            }
            
            .stat-label {
                font-size: 9px;
            }
            
            .stat-value {
                font-size: 14px;
            }
            
            .home-button {
                width: 40px;
                height: 40px;
                font-size: 18px;
                bottom: 15px;
                right: 15px;
            }
            
            .info-item .info-message {
                -webkit-line-clamp: 3;
            }
        }

        /* ========== INFORMATION SECTION - STYLE BARU ========== */
        .info-card {
            max-height: 300px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .info-list {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: none; /* Hide scrollbar for Firefox */
            -ms-overflow-style: none; /* Hide scrollbar for IE/Edge */
        }

        .info-list::-webkit-scrollbar {
            display: none; /* Hide scrollbar for Chrome/Safari */
        }

        .info-item {
            background: rgba(0, 173, 181, 0.08);
            border-left: 3px solid #00adb5;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 8px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .info-item.urgent {
            border-left-color: #ff416c;
            background: rgba(255, 65, 108, 0.08);
        }

        .info-item.assigned {
            border-left-color: #ffa726;
            background: rgba(255, 167, 38, 0.08);
        }

        .info-content {
            display: flex;
            gap: 10px;
        }

        .info-icon {
            width: 24px;
            height: 24px;
            min-width: 24px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            background: rgba(0, 173, 181, 0.15);
            color: #00adb5;
            margin-top: 2px;
        }

        .info-item.urgent .info-icon {
            background: rgba(255, 65, 108, 0.15);
            color: #ff416c;
        }

        .info-item.assigned .info-icon {
            background: rgba(255, 167, 38, 0.15);
            color: #ffa726;
        }

        .info-details {
            flex: 1;
            min-width: 0;
        }

        .info-from-to {
            font-size: 12px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 5px;
        }

        .info-status-badge {
            margin-bottom: 8px;
        }

        .info-message {
            color: #d1d9e6;
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 8px;
            word-break: break-word;
        }

        .info-meta {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #a9b7c6;
        }

        .info-time, .info-date {
            display: flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>
    
    <!-- ========== HEADER ========== -->
    <header class="dashboard-header">
        <div class="logo-container">
            <img src="assets/img/logo-denso.png" alt="DENSO Logo" onerror="this.style.display='none'">
            <div class="header-title">
                <div class="main-title">🚀 LIVE DASHBOARD - BO CONTROL MONITORING</div>
                <div class="sub-title">Real-time Tracking • Operator View • Instant Updates</div>
            </div>
        </div>
    
        
        <div class="datetime-display">
            <div class="date-display" id="dateDisplay">Loading date...</div>
            <div class="time-display" id="timeDisplay">00:00:00</div>
        </div>
    </header>
        
    <!-- ========== MAIN DASHBOARD ========== -->
    <div class="dashboard-container">
        
        <!-- LEFT PANEL - REAL-TIME CHARTS -->
        <div class="live-charts-container">
            
            <!-- TODAY'S TARGET GAUGE -->
            <div class="live-gauge-card">
                <div class="gauge-header">
                    <h6><i class="fas fa-tachometer-alt"></i> TODAY'S ACHIEVEMENT</h6>
                    <span class="live-time" id="lastUpdateTime">
                        <span class="loading-spinner"></span> Updating...
                    </span>
                </div>
                <div class="gauge-container">
                    <div id="todayGauge" style="height: 180px; margin-top: 10px;">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-3 text-white small">Loading achievement data...</p>
                        </div>
                    </div>
                    <div class="gauge-stats">
                        <div class="stat">
                            <span class="label">Target</span>
                            <span class="value" id="targetQty">0 pcs</span>
                        </div>
                        <div class="stat">
                            <span class="label">Incoming</span>
                            <span class="value text-success" id="incomingQty">0 pcs</span>
                        </div>
                        <div class="stat">
                            <span class="label">Balance</span>
                            <span class="value" id="balanceQty">0 pcs</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- HOURLY PROGRESS -->
            <div class="hourly-card">
                <div class="hourly-header">
                    <h6><i class="fas fa-clock"></i> HOURLY INCOMING PROGRESS (pcs)</h6>
                    <div class="shift-badge" id="currentShift">D/S: 07:00-20:00</div>
                </div>
                <div id="hourlyChart" style="height: 200px;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-3 text-white small">Loading hourly data...</p>
                    </div>
                </div>
            </div>
            
            <!-- ========== INFORMATION SECTION ========== -->
            <div class="info-card">
                <div class="info-header">
                    <h6><i class="fas fa-bullhorn"></i> LIVE INFORMATION</h6>
                    <span class="badge bg-warning rounded-pill px-2" id="infoCount">0</span>
                </div>
                
                <div class="info-list" id="informationList">
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <p class="mt-2 text-white small">Loading live information...</p>
                        <small class="text-muted">Fetching from last 7 days</small>
                    </div>
                </div>
            
            </div>
            
            <!-- CONTROL BUTTONS -->
            <div class="control-buttons">
                <button class="control-btn control-btn-primary" onclick="refreshAllData()" id="refreshBtn">
                    <i class="fas fa-sync-alt"></i> <span id="refreshText">Refresh Now</span>
                </button>
                <button class="control-btn control-btn-secondary" onclick="toggleAutoRefresh()" id="autoRefreshBtn">
                    <i class="fas fa-power-off"></i> <span id="autoRefreshText">Auto: ON</span>
                </button>
            </div>
            
        </div>
        
        <!-- RIGHT PANEL - DATA TABLE -->
        <div class="main-data-panel">
            <div class="panel-header">
                <div class="panel-title">
                    <i class="fas fa-truck-loading"></i> LIVE SUPPLIER DELIVERY STATUS
                    <div class="live-badge">
                        <div class="live-dot"></div>
                        LIVE • <span id="todayDate">Today</span>
                    </div>
                </div>
                
                <div class="panel-stats">
                    <div class="stat-item">
                        <span class="stat-label">TOTAL</span>
                        <span class="stat-value" id="totalSuppliers">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">COMPLETED</span>
                        <span class="stat-value text-success" id="completedCount">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">ON PROGRESS</span>
                        <span class="stat-value text-primary" id="onProgressCount">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">DELAYED</span>
                        <span class="stat-value text-warning" id="delayedCount">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">OVER</span>
                        <span class="stat-value text-danger" id="overCount">0</span>
                    </div>
                </div>
            </div>
            
            <!-- NEW TABLE DESIGN -->
            <div class="table-wrapper">
                <div class="table-container">
                    <div class="table-fixed-header">
                        <table>
                            <thead>
                                <tr>
                                    <th><i class="fas fa-barcode"></i> CODE</th>
                                    <th><i class="fas fa-warehouse"></i> SUPPLIER</th>
                                    <th><i class="fas fa-user"></i> PIC</th>
                                    <th><i class="fas fa-sun"></i> DAY</th>
                                    <th><i class="fas fa-moon"></i> NIGHT</th>
                                    <th><i class="fas fa-truck"></i> ORDER</th>
                                    <th><i class="fas fa-box"></i> INCOMING</th>
                                    <th><i class="fas fa-balance-scale"></i> REMAIN</th>
                                    <th><i class="fas fa-chart-line"></i> RATE</th>
                                    <th><i class="fas fa-flag"></i> STATUS</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="table-scroll-body" id="tableScrollBody">
                        <div class="scrolling-content" id="scrollingContent">
                            <!-- Data akan dimasukkan disini -->
                            <div class="empty-state">
                                <i class="fas fa-database"></i>
                                <p>Loading supplier data...</p>
                                <small>Please wait while data is being loaded</small>
                            </div>
                        </div>
                    </div>
                    <div class="scroll-controls">
                        <button class="scroll-btn" onclick="toggleAutoScroll()" id="autoScrollBtn">
                            <i class="fas fa-pause"></i> Pause Scroll
                        </button>
                        <button class="scroll-btn" onclick="scrollFaster()">
                            <i class="fas fa-forward"></i> Faster
                        </button>
                        <button class="scroll-btn" onclick="scrollSlower()">
                            <i class="fas fa-backward"></i> Slower
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- HOME BUTTON -->
    <a href="index.php" class="home-button" title="Back to Main Dashboard">
        <i class="fas fa-home"></i>
    </a>
    
    <!-- ========== JAVASCRIPT ========== -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.js"></script>

    <script>
    // ========== GLOBAL VARIABLES ==========
    let todayGauge = null;
    let hourlyChart = null;
    let autoRefreshInterval = null;
    let isAutoRefresh = true;
    let today = new Date().toISOString().split('T')[0].replace(/-/g, '');

    // ========== SCROLLING ANIMATION VARIABLES ==========
    let autoScrollInterval = null;
    let isAutoScrolling = true;
    let scrollSpeed = 0.8;
    let tableScrollPosition = 0; // ⬅️ GANTI NAMA, JANGAN scrollPosition
    let scrollDirection = 1;

    // ========== INFORMATION SCROLL VARIABLES ==========
    let infoScrollInterval = null;
    let isInfoScrolling = true;
    let infoScrollPosition = 0; // ⬅️ GANTI NAMA, JANGAN scrollPosition
    let infoScrollSpeed = 0.5;

    // ========== DATE TIME FUNCTIONS ==========
    function updateDateTime() {
        const now = new Date();
        const dateStr = now.toLocaleDateString('en-US', { 
            weekday: 'short', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        const timeStr = now.toLocaleTimeString('en-US', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        
        $('#dateDisplay').text(dateStr);
        $('#timeDisplay').text(timeStr);
        $('#todayDate').text(now.toLocaleDateString('en-US', { day: 'numeric', month: 'short' }));
        
        // Update current shift
        const hour = now.getHours();
        if (hour >= 7 && hour <= 20) {
            $('#currentShift').text('D/S: 07:00-20:00')
                .css('background', 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)');
        } else {
            $('#currentShift').text('N/S: 21:00-06:00')
                .css('background', 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)');
        }
    }

    // ========== INFORMATION SECTION - FORMAT BARU DENGAN AUTO SCROLL ==========
    function updateInformation() {
        console.log('📢 Loading informasi untuk live dashboard...');
        
        $.ajax({
            url: 'api/get_live_information.php',
            type: 'GET',
            dataType: 'json',
            timeout: 8000,
            beforeSend: function() {
                $('#informationList').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <p class="mt-2 text-white small">Loading informasi...</p>
                    </div>
                `);
            },
            success: function(response) {
                console.log('✅ Informasi diterima:', response.count, 'items');
                
                if (!response || !response.success || !response.informations) {
                    showNoInformation();
                    return;
                }
                
                const informations = response.informations;
                
                if (informations.length === 0) {
                    showNoInformation();
                    return;
                }
                
                // RENDER INFORMASI DENGAN FORMAT BARU
                let html = '';
                
                informations.forEach((info) => {
                    // Tentukan styling berdasarkan status
                    let statusClass = '';
                    let statusBadge = '';
                    let icon = 'info-circle';
                    
                    if (info.STATUS === 'Open') {
                        statusClass = 'urgent';
                        statusBadge = 'bg-danger';
                        icon = 'exclamation-triangle';
                    } else if (info.STATUS === 'On Progress') {
                        statusClass = 'assigned';
                        statusBadge = 'bg-warning';
                        icon = 'clock';
                    }
                    
                    // Format: PENGIRIM → PENERIMA
                    const fromToText = info.PIC_FROM + ' → ' + info.PIC_TO;
                    
                    // Isi informasi (Request atau Item)
                    const messageText = info.REQUEST || info.ITEM || 'Tidak ada isi';
                    
                    html += `
                    <div class="info-item ${statusClass}">
                        <div class="info-content">
                            <div class="info-icon">
                                <i class="fas fa-${icon}"></i>
                            </div>
                            <div class="info-details">
                                <!-- PENGIRIM → PENERIMA -->
                                <div class="info-from-to">
                                    <strong>${fromToText}</strong>
                                </div>
                                
                                <!-- STATUS BADGE -->
                                <div class="info-status-badge">
                                    <span class="badge ${statusBadge}">${info.status_text || 'OPEN'}</span>
                                </div>
                                
                                <!-- ISI INFORMASI -->
                                <div class="info-message">
                                    ${messageText}
                                </div>
                                
                                <!-- WAKTU & TANGGAL -->
                                <div class="info-meta">
                                    <div class="info-time">
                                        <i class="far fa-clock"></i> ${info.time_formatted || ''}
                                    </div>
                                    <div class="info-date">
                                        ${info.date_formatted || ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
                
                $('#informationList').html(html);
                $('#infoCount').text(informations.length);
                
                // Mulai auto scroll untuk informasi
                startInfoAutoScroll();
                
            },
            error: function(xhr, status, error) {
                console.error('❌ Error load informasi:', error);
                $('#informationList').html(`
                    <div class="text-center py-4 text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p class="mt-2 text-white small">Gagal load informasi</p>
                        <button onclick="updateInformation()" class="btn btn-sm btn-outline-warning mt-2">
                            <i class="fas fa-redo"></i> Retry
                        </button>
                    </div>
                `);
                $('#infoCount').text('!').removeClass('bg-warning bg-primary').addClass('bg-danger');
            }
        });
    }

    // ========== AUTO SCROLL FUNCTIONS UNTUK INFORMATION ==========
    function startInfoAutoScroll() {
        const container = $('#informationList');
        const items = container.find('.info-item');
        
        if (items.length === 0) return;
        
        // Reset scroll position
        container.scrollTop(0);
        infoScrollPosition = 0;
        
        // Clear existing interval
        if (infoScrollInterval) {
            clearInterval(infoScrollInterval);
        }
        
        // Start new scrolling
        infoScrollInterval = setInterval(() => {
            if (!isInfoScrolling) return;
            
            const containerHeight = container.height();
            const contentHeight = container[0].scrollHeight;
            
            // Scroll up slowly
            infoScrollPosition += infoScrollSpeed;
            
            // If reached bottom, reset to top
            if (infoScrollPosition > contentHeight - containerHeight) {
                infoScrollPosition = 0;
                container.scrollTop(0);
                
                // Refresh data after full scroll
                setTimeout(() => {
                    updateInformation();
                    console.log('🔄 Refresh informasi setelah scroll selesai');
                }, 3000);
            } else {
                container.scrollTop(infoScrollPosition);
            }
        }, 50);
    }

    function stopInfoAutoScroll() {
        if (infoScrollInterval) {
            clearInterval(infoScrollInterval);
            infoScrollInterval = null;
        }
    }

    function toggleInfoAutoScroll() {
        isInfoScrolling = !isInfoScrolling;
        const btn = $('#infoScrollBtn');
        
        if (isInfoScrolling) {
            startInfoAutoScroll();
            btn.html('<i class="fas fa-pause"></i> <span>Pause Scroll</span>');
            btn.removeClass('active');
        } else {
            stopInfoAutoScroll();
            btn.html('<i class="fas fa-play"></i> <span>Play Scroll</span>');
            btn.addClass('active');
        }
    }

    function showNoInformation() {
        $('#informationList').html(`
            <div class="text-center py-5">
                <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                <p class="mt-3 text-white">Tidak ada informasi Open/On Progress</p>
                <small class="text-muted">Semua informasi sudah selesai</small>
            </div>
        `);
        $('#infoCount').text('0').removeClass('bg-danger bg-warning').addClass('bg-success');
    }

    // ========== HOURLY PROGRESS ==========
    function updateHourlyChart() {
        console.log('⏰ Updating hourly chart...');
        
        const currentHour = new Date().getHours();
        const isDayShift = currentHour >= 7 && currentHour <= 20;
        
        $.ajax({
            url: 'api/get_hourly_progress.php',
            type: 'GET',
            data: { 
                date: today,
                shift: isDayShift ? 'DS' : 'NS'
            },
            dataType: 'json',
            timeout: 5000,
            success: function(response) {
                if (!response || !Array.isArray(response)) {
                    console.warn('⚠️ No hourly data available');
                    $('#hourlyChart').html('<div class="text-center py-5"><i class="fas fa-clock text-muted" style="font-size: 2rem;"></i><p class="mt-2 text-white small">No hourly data available</p></div>');
                    return;
                }
                
                console.log('✅ Hourly data received:', response.length, 'records');
                
                let cumulative = 0;
                const hours = response.map(r => r.hour + ':00');
                const quantities = response.map(r => {
                    cumulative += r.qty;
                    return cumulative;
                });
                
                const options = {
                    series: [{
                        name: 'Cumulative Incoming',
                        data: quantities
                    }],
                    chart: {
                        type: 'area',
                        height: 200,
                        toolbar: { show: false },
                        animations: { 
                            enabled: true, 
                            speed: 800
                        },
                        zoom: { enabled: false }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.3,
                            stops: [0, 90, 100]
                        }
                    },
                    colors: ['#00adb5'],
                    dataLabels: {
                        enabled: false
                    },
                    grid: {
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        strokeDashArray: 3,
                        yaxis: { lines: { show: true } },
                        xaxis: { lines: { show: true } }
                    },
                    xaxis: {
                        categories: hours,
                        labels: { 
                            style: { colors: '#a9b7c6', fontSize: '11px' },
                            rotate: -45
                        },
                        title: { 
                            text: 'Hour', 
                            style: { color: '#a9b7c6', fontSize: '12px' }
                        }
                    },
                    yaxis: {
                        title: { 
                            text: 'Cumulative (pcs)', 
                            style: { color: '#a9b7c6', fontSize: '12px' }
                        },
                        labels: { 
                            style: { colors: '#a9b7c6', fontSize: '11px' },
                            formatter: function(val) { return val.toLocaleString(); }
                        }
                    },
                    tooltip: {
                        y: { 
                            formatter: function(val, { dataPointIndex }) {
                                const current = quantities[dataPointIndex];
                                const previous = dataPointIndex > 0 ? quantities[dataPointIndex-1] : 0;
                                const increment = current - previous;
                                return `${current.toLocaleString()} pcs (+${increment.toLocaleString()})`;
                            }
                        }
                    },
                    markers: {
                        size: 4,
                        colors: ['#ffffff'],
                        strokeColors: '#00adb5',
                        strokeWidth: 2
                    }
                };
                
                if (!hourlyChart) {
                    hourlyChart = new ApexCharts(document.querySelector("#hourlyChart"), options);
                    hourlyChart.render();
                } else {
                    hourlyChart.updateOptions(options);
                    hourlyChart.updateSeries(options.series);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Failed to load hourly data:', error);
                $('#hourlyChart').html('<div class="text-center py-5 text-warning"><i class="fas fa-exclamation-triangle"></i><p class="mt-2 text-white small">Failed to load hourly data</p></div>');
            }
        });
    }

    // ========== TODAY'S GAUGE ==========
    function updateTodayGauge() {
        console.log('📊 Updating today gauge...');
        
        $.ajax({
            url: 'api/get_today_performance.php',
            type: 'GET',
            data: { date: today },
            dataType: 'json',
            timeout: 5000,
            beforeSend: function() {
                $('#lastUpdateTime').html('<span class="loading-spinner"></span> Updating...');
            },
            success: function(response) {
                if (!response) {
                    console.error('❌ No response from today performance API');
                    return;
                }
                
                const totalOrder = parseInt(response.total_order) || 0;
                const totalIncoming = parseInt(response.total_incoming) || 0;
                const achievement = totalOrder > 0 ? Math.min(Math.round((totalIncoming / totalOrder) * 100), 100) : 0;
                const balance = totalOrder - totalIncoming;
                
                console.log('✅ Today stats:', { totalOrder, totalIncoming, achievement, balance });
                
                if (!todayGauge) {
                    todayGauge = new ApexCharts(document.querySelector("#todayGauge"), {
                        series: [achievement],
                        chart: { 
                            type: 'radialBar', 
                            height: 180,
                            animations: { enabled: true, speed: 1000 }
                        },
                        plotOptions: {
                            radialBar: {
                                startAngle: -90,
                                endAngle: 90,
                                hollow: { size: '65%' },
                                track: { background: 'rgba(255, 255, 255, 0.1)' },
                                dataLabels: {
                                    name: { 
                                        show: false 
                                    },
                                    value: { 
                                        fontSize: '32px',
                                        fontWeight: 'bold',
                                        offsetY: 5,
                                        formatter: function(val) { 
                                            return Math.min(val, 100) + '%'; 
                                        },
                                        color: '#ffffff'
                                    }
                                }
                            }
                        },
                        colors: [
                            achievement >= 90 ? '#2ecc71' : 
                            achievement >= 70 ? '#f1c40f' : 
                            achievement >= 50 ? '#e67e22' : '#e74c3c'
                        ],
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shade: 'dark',
                                type: 'horizontal',
                                gradientToColors: [
                                    achievement >= 90 ? '#27ae60' : 
                                    achievement >= 70 ? '#f39c12' : 
                                    achievement >= 50 ? '#d35400' : '#c0392b'
                                ],
                                stops: [0, 100]
                            }
                        }
                    });
                    todayGauge.render();
                } else {
                    todayGauge.updateSeries([achievement]);
                    todayGauge.updateOptions({
                        colors: [
                            achievement >= 90 ? '#2ecc71' : 
                            achievement >= 70 ? '#f1c40f' : 
                            achievement >= 50 ? '#e67e22' : '#e74c3c'
                        ]
                    });
                }
                
                $('#targetQty').text(totalOrder.toLocaleString() + ' pcs');
                $('#incomingQty').text(totalIncoming.toLocaleString() + ' pcs');
                $('#balanceQty').text(balance.toLocaleString() + ' pcs');
                $('#lastUpdateTime').text('Last: ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
            },
            error: function(xhr, status, error) {
                console.error('❌ Failed to load today performance:', error);
                $('#lastUpdateTime').html('<i class="fas fa-exclamation-triangle text-warning"></i> Failed');
                
                $('#targetQty').text('0 pcs');
                $('#incomingQty').text('0 pcs');
                $('#balanceQty').text('0 pcs');
            }
        });
    }

    // ========== LIVE DATA TABLE ==========
    function updateLiveTable() {
        console.log('📋 Updating live table...');
        
        $.ajax({
            url: 'api/get_live_supplier_data.php',
            type: 'GET',
            data: { date: today },
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                console.log('✅ Live table response received');
                
                if (Array.isArray(response)) {
                    renderTableWithFixedHeader(response);
                } else if (response && response.data) {
                    renderTableWithFixedHeader(response.data);
                } else {
                    renderTableWithFixedHeader(response);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Live table API failed:', status, error);
                
                const demoData = generateDemoData();
                renderTableWithFixedHeader(demoData);
                
                showToast('warning', 'Using demo data - API connection failed');
            }
        });
    }

    // ========== RENDER TABLE FUNCTIONS ==========
function renderTableWithFixedHeader(data) {
    if (!data || !Array.isArray(data) || data.length === 0) {
        console.warn('⚠️ No data to render in table');
        $('#scrollingContent').html(`
            <div class="empty-state">
                <i class="fas fa-database"></i>
                <p>No supplier data available for today</p>
                <small>Check if data exists in T_ORDER table</small>
            </div>
        `);
        updateStats(0, 0, 0, 0, 0);
        return;
    }
    
    console.log(`📊 Rendering ${data.length} supplier records`);
    
    // Sort data: ON_PROGRESS -> DELAY -> OVER -> OK
    data.sort((a, b) => {
        const statusOrder = {
            'ON_PROGRESS': 1,
            'DELAY': 2,
            'OVER': 3,
            'OK': 4
        };
        
        const statusA = a.STATUS || 'ON_PROGRESS';
        const statusB = b.STATUS || 'ON_PROGRESS';
        
        return statusOrder[statusA] - statusOrder[statusB];
    });
    
    // Duplicate data untuk scrolling seamless
    const duplicateCount = 6;
    let duplicatedData = [];
    for (let i = 0; i < duplicateCount; i++) {
        duplicatedData = duplicatedData.concat(data);
    }
    
    let html = '<table>';
    
    let totalSuppliers = 0;
    let completedCount = 0;
    let onProgressCount = 0;
    let delayedCount = 0;
    let overCount = 0;
    
    // Hitung stats dari data asli
    data.forEach((item) => {
        totalSuppliers++;
        const status = item.STATUS || 'ON_PROGRESS';
        
        switch(status) {
            case 'OK':
                completedCount++;
                break;
            case 'OVER':
                overCount++;
                break;
            case 'DELAY':
                delayedCount++;
                break;
            case 'ON_PROGRESS':
            default:
                onProgressCount++;
                break;
        }
    });
    
    // Render semua data
    duplicatedData.forEach((item, index) => {
        const completionRate = parseFloat(item.completion_rate) || 0;
        const status = item.STATUS || 'ON_PROGRESS';
        
        // Determine status class
        let statusClass = '';
        switch(status) {
            case 'OK':
                statusClass = 'status-ok';
                break;
            case 'OVER':
                statusClass = 'status-over';
                break;
            case 'DELAY':
                statusClass = 'status-delay';
                break;
            case 'ON_PROGRESS':
            default:
                statusClass = 'status-on-progress';
                break;
        }
        
        // Determine rate color
        let rateClass = '';
        if (completionRate >= 90) {
            rateClass = 'rate-good';
        } else if (completionRate >= 70) {
            rateClass = 'rate-warning';
        } else {
            rateClass = 'rate-danger';
        }
        
        // Determine quantity colors
        const orderQty = item.total_order || 0;
        const incomingQty = item.total_incoming || 0;
        const remainQty = item.balance || 0;
        
        let orderClass = 'quantity-good';
        let incomingClass = 'quantity-warning';
        let remainClass = remainQty > 0 ? 'quantity-danger' : 'quantity-good';
        
        if (status === 'OK') {
            orderClass = 'quantity-good';
            incomingClass = 'quantity-good';
            remainClass = 'quantity-good';
        } else if (status === 'OVER') {
            orderClass = 'quantity-good';
            incomingClass = 'quantity-danger';
            remainClass = 'quantity-danger';
        }
        
        // Format persentase
        const dsPercent = Math.min(item.ds_completion || 0, 100);
        const nsPercent = Math.min(item.ns_completion || 0, 100);
        
        html += `
        <tr>
            <td><span class="supplier-code">${item.supplier_code || 'N/A'}</span></td>
            <td><div class="supplier-name" title="${item.supplier_name || 'Unknown'}">${item.supplier_name || 'Unknown'}</div></td>
            <td><span class="pic-badge">${item.pic_order || '-'}</span></td>
            <td class="progress-cell">
                <div class="progress-container">
                    <div class="progress-info">
                        <div class="progress-label">
                            ${dsPercent}%
                        </div>
                        <div class="progress-bar-horizontal">
                            <div class="progress-fill ds" style="width: ${dsPercent}%"></div>
                        </div>
                    </div>
                </div>
            </td>
            <td class="progress-cell">
                <div class="progress-container">
                    <div class="progress-info">
                        <div class="progress-label">
                            ${nsPercent}%
                        </div>
                        <div class="progress-bar-horizontal">
                            <div class="progress-fill ns" style="width: ${nsPercent}%"></div>
                        </div>
                    </div>
                </div>
            </td>
            <td class="quantity-cell">
                <div class="quantity-display ${orderClass}">
                    <div class="quantity-value">${orderQty.toLocaleString()}</div>
                    <div class="quantity-label">Order</div>
                </div>
            </td>
            <td class="quantity-cell">
                <div class="quantity-display ${incomingClass}">
                    <div class="quantity-value">${incomingQty.toLocaleString()}</div>
                    <div class="quantity-label">Incoming</div>
                </div>
            </td>
            <td class="quantity-cell">
                <div class="quantity-display ${remainClass}">
                    <div class="quantity-value">${remainQty.toLocaleString()}</div>
                    <div class="quantity-label">Remain</div>
                </div>
            </td>
            <td><span class="rate-display ${rateClass}">${completionRate.toFixed(0)}%</span></td>
            <td><span class="status-badge ${statusClass}">${status}</span></td>
        </tr>`;
    });
    
    html += '</table>';
    
    $('#scrollingContent').html(html);
    updateStats(totalSuppliers, completedCount, onProgressCount, delayedCount, overCount);
    
    console.log('✅ Table rendered successfully');
    
    // Setup animation setelah konten dimuat
    setTimeout(() => {
        if (isAutoScrolling) {
            startAutoScroll();
        }
    }, 100);
}

function updateStats(total, completed, onProgress, delayed, over) {
    $('#totalSuppliers').text(total);
    $('#completedCount').text(completed);
    $('#onProgressCount').text(onProgress);
    $('#delayedCount').text(delayed);
    $('#overCount').text(over);
}

function generateDemoData() {
    console.log('🔄 Generating demo data for fallback');
    
    const suppliers = [
        { code: 'C60', name: 'AUTOPLASTIK INDONESIA,PT.', pic: 'SATRIO' },
        { code: 'A25', name: 'CHANDRA NUGERAHCIPTA, PT.', pic: 'EKA' },
        { code: 'B79', name: 'YUJU INDONESIA, PT.', pic: 'SATRIO' },
        { code: 'B70', name: 'DHARMA PRECISION PARTS,PT', pic: 'EKO' },
        { code: 'C40', name: 'CIPTAJAYA KREASINDO U.,PT', pic: 'EKO' },
        { code: 'D15', name: 'PRIMA TEKNIK INDONESIA', pic: 'ALBERTO' },
        { code: 'E22', name: 'SUMBER MAKMUR SEJAHTERA', pic: 'MURSID' },
        { code: 'F33', name: 'TECHNO PRECISION PARTS', pic: 'SATRIO' },
        { code: 'G44', name: 'MITRA USAHA BERSAMA', pic: 'EKA' },
        { code: 'H55', name: 'GLOBAL MANUFACTURING IND.', pic: 'EKO' }
    ];
    
    return suppliers.map(supplier => {
        const orderQty = Math.floor(Math.random() * 500) + 100;
        const incomingQty = Math.floor(Math.random() * orderQty * 1.2);
        const dsIncoming = Math.floor(incomingQty * 0.6);
        const nsIncoming = Math.floor(incomingQty * 0.4);
        const completionRate = Math.round((incomingQty / orderQty) * 100);
        
        let status = 'ON_PROGRESS';
        if (completionRate >= 100) status = 'OK';
        else if (completionRate >= 90) status = 'ON_PROGRESS';
        else if (completionRate >= 70) status = 'DELAY';
        else status = 'OVER';
        
        return {
            supplier_code: supplier.code,
            supplier_name: supplier.name,
            pic_order: supplier.pic,
            total_order: orderQty,
            total_incoming: incomingQty,
            ds_incoming: dsIncoming,
            ns_incoming: nsIncoming,
            ds_completion: Math.round((dsIncoming / orderQty) * 100),
            ns_completion: Math.round((nsIncoming / orderQty) * 100),
            completion_rate: completionRate,
            balance: orderQty - incomingQty,
            STATUS: status
        };
    });
}

    // ========== SCROLLING FUNCTIONS UNTUK SUPPLIER TABLE ==========
    function startAutoScroll() {
        if (autoScrollInterval) clearInterval(autoScrollInterval);
        
        const scrollBody = document.getElementById('tableScrollBody');
        const content = document.getElementById('scrollingContent');
        
        if (!scrollBody || !content) {
            console.error('❌ Scroll elements not found');
            return;
        }
        
        tableScrollPosition = 0;
        
        autoScrollInterval = setInterval(() => {
            if (!isAutoScrolling) return;
            
            tableScrollPosition += scrollSpeed * scrollDirection;
            const contentHeight = content.scrollHeight;
            const containerHeight = scrollBody.clientHeight;
            const maxScroll = contentHeight - containerHeight;
            
            if (tableScrollPosition >= maxScroll) {
                tableScrollPosition = 0;
                scrollBody.scrollTop = 0;
            } else {
                scrollBody.scrollTop = tableScrollPosition;
            }
        }, 16);
        
        console.log('🔄 Auto scroll started');
    }

    function stopAutoScroll() {
        if (autoScrollInterval) {
            clearInterval(autoScrollInterval);
            autoScrollInterval = null;
            console.log('⏸️ Auto scroll stopped');
        }
    }

    function toggleAutoScroll() {
        isAutoScrolling = !isAutoScrolling;
        const btn = document.getElementById('autoScrollBtn');
        
        if (isAutoScrolling) {
            startAutoScroll();
            btn.innerHTML = '<i class="fas fa-pause"></i> Pause Scroll';
            btn.classList.add('active');
        } else {
            stopAutoScroll();
            btn.innerHTML = '<i class="fas fa-play"></i> Play Scroll';
            btn.classList.remove('active');
        }
    }

    function scrollFaster() {
        scrollSpeed = Math.min(scrollSpeed + 0.2, 3);
        console.log(`⚡ Scroll speed: ${scrollSpeed.toFixed(1)}`);
        showToast('info', `Scroll speed: ${scrollSpeed.toFixed(1)}x`);
    }

    function scrollSlower() {
        scrollSpeed = Math.max(scrollSpeed - 0.2, 0.5);
        console.log(`🐢 Scroll speed: ${scrollSpeed.toFixed(1)}`);
        showToast('info', `Scroll speed: ${scrollSpeed.toFixed(1)}x`);
    }

    // ========== AUTO REFRESH FUNCTIONS ==========
    function refreshAllData() {
        console.log('🔄 Manual refresh triggered');
        
        const refreshBtn = $('#refreshBtn');
        const originalText = refreshBtn.html();
        refreshBtn.prop('disabled', true);
        refreshBtn.html('<span class="loading-spinner"></span> Refreshing...');
        
        updateTodayGauge();
        updateHourlyChart();
        updateInformation(); // ⬅️ INI YANG PENTING!
        updateLiveTable();
        updateDateTime();
        
        setTimeout(() => {
            refreshBtn.prop('disabled', false);
            refreshBtn.html(originalText);
            showToast('success', 'Data refreshed successfully');
        }, 2000);
    }

    function toggleAutoRefresh() {
        isAutoRefresh = !isAutoRefresh;
        const btn = $('#autoRefreshBtn');
        const text = $('#autoRefreshText');
        
        if (isAutoRefresh) {
            startAutoRefresh();
            text.text('Auto: ON');
            btn.removeClass('control-btn-secondary').addClass('control-btn-primary');
            showToast('success', 'Auto refresh enabled (every 8 minutes)');
        } else {
            stopAutoRefresh();
            text.text('Auto: OFF');
            btn.removeClass('control-btn-primary').addClass('control-btn-secondary');
            showToast('warning', 'Auto refresh disabled');
        }
    }

    function startAutoRefresh() {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        autoRefreshInterval = setInterval(refreshAllData, 8 * 60 * 1000);
        console.log('🔄 Auto refresh started (8 minutes interval)');
    }

    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
            console.log('⏸️ Auto refresh stopped');
        }
    }

    // ========== TOAST NOTIFICATION ==========
    function showToast(type, message) {
        $('.live-toast').remove();
        
        const iconMap = {
            'success': 'check-circle',
            'error': 'exclamation-triangle',
            'warning': 'exclamation-circle',
            'info': 'info-circle'
        };
        
        const colorMap = {
            'success': '#2ecc71',
            'error': '#e74c3c',
            'warning': '#f39c12',
            'info': '#3498db'
        };
        
        const icon = iconMap[type] || 'info-circle';
        const color = colorMap[type] || '#3498db';
        
        const toast = $(`
            <div class="live-toast" style="
                position: fixed;
                top: 100px;
                right: 20px;
                background: rgba(22, 33, 62, 0.95);
                border-left: 4px solid ${color};
                color: white;
                padding: 12px 16px;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                backdrop-filter: blur(10px);
                z-index: 9999;
                min-width: 300px;
                max-width: 400px;
                transform: translateX(120%);
                transition: transform 0.3s ease;
                display: flex;
                align-items: center;
                gap: 10px;
            ">
                <i class="fas fa-${icon}" style="color: ${color}; font-size: 18px;"></i>
                <div style="flex: 1;">
                    <div style="font-weight: 600; margin-bottom: 2px; text-transform: capitalize;">${type}</div>
                    <div style="font-size: 12px; color: #a9b7c6;">${message}</div>
                </div>
                <button onclick="$(this).closest('.live-toast').remove()" style="
                    background: none;
                    border: none;
                    color: #a9b7c6;
                    cursor: pointer;
                    padding: 0;
                    font-size: 14px;
                ">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);
        
        $('body').append(toast);
        
        setTimeout(() => {
            toast.css('transform', 'translateX(0)');
        }, 10);
        
        setTimeout(() => {
            toast.css('transform', 'translateX(120%)');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // ========== INITIALIZATION ==========
    $(document).ready(function() {
        console.log('🚀 LIVE DASHBOARD INITIALIZING...');
        console.log('📅 Today:', today);
        
        // Initial data load
        updateDateTime();
        updateTodayGauge();
        updateHourlyChart();
        updateInformation(); // ⬅️ INI YANG UTAMA!
        updateLiveTable();
        
        // Start auto refresh
        startAutoRefresh();
        
        // Update time every second
        setInterval(updateDateTime, 1000);
        
        // Handle page visibility
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                console.log('📱 Page hidden, pausing updates');
                stopAutoRefresh();
                stopAutoScroll();
                stopInfoAutoScroll();
                showToast('info', 'Dashboard paused');
            } else {
                console.log('📱 Page visible, resuming updates');
                if (isAutoRefresh) startAutoRefresh();
                if (isAutoScrolling) startAutoScroll();
                if (isInfoScrolling) startInfoAutoScroll();
                
                setTimeout(refreshAllData, 500);
                showToast('success', 'Dashboard resumed');
            }
        });
        
        console.log('✅ LIVE DASHBOARD INITIALIZED SUCCESSFULLY');
        showToast('success', 'Live Dashboard loaded');
    });
    </script>

</body>
</html>