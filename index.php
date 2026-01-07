<?php
// =============================================================================
// Session Initialization - Must be at the very top before any output
// =============================================================================
session_start();

// Get user ID from URL parameter
$urlUserId = isset($_GET['as']) ? $_GET['as'] : null;
$snippetId = isset($_GET['snippet']) ? $_GET['snippet'] : null;

// Redirect URL for unauthenticated users
$redirectUrl = 'https://j100coders.org/coder/codelab.php';

// Initialize or validate session
if ($urlUserId !== null && !empty($urlUserId) && $urlUserId !== 'anonymous') {
    // User came with ?as= parameter, store in session
    $_SESSION['user_id'] = $urlUserId;
    $_SESSION['session_start'] = time();
    $_SESSION['last_activity'] = time();
} elseif (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // User has existing session, update last activity
    $_SESSION['last_activity'] = time();
} else {
    // No user ID and no session - redirect to login
    header("Location: $redirectUrl");
    exit();
}

// Get current user ID for JavaScript
$currentUserId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>J100 Coding Sandbox</title>
    <style>
        :root {
            --primary-color: #3966a7;
            --secondary-color: #0099ff;
            --accent-color: #667eea;
            --bg-dark: #0f0f23;
            --bg-darker: #0d0d15;
            --bg-card: rgba(17, 17, 27, 0.95);
            --border-color: #3a3a52;
            --text-primary: #e0e0e0;
            --text-secondary: #a0a0b0;
            --success-color: #4ade80;
            --error-color: #f87171;
            --warning-color: #facc15;
            --info-color: #60a5fa;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.3);
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #000;
            color: var(--text-primary);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 300px;
            background: radial-gradient(circle at 50% 0%, rgba(0, 212, 255, 0.1), transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: bold;
            transition: var(--transition-normal);
            cursor: pointer;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo-text {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 50%, var(--accent-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            background-size: 200% auto;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .logo::before {
            content: "{ }";
            color: var(--primary-color);
            font-size: 28px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        #controls {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        select,
        button {
            padding: 10px 10px;
            background: transparent;
            border: none;
            border-radius: 5%;
            color: white;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        button:active::before {
            width: 300px;
            height: 300px;
        }

        select {
            background: var(--primary-color);
        }

        button {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 153, 255, 0.4);
            border-color: var(--primary-color);
        }

        button:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 153, 255, 0.3);
        }

        button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            filter: grayscale(1);
            transform: none !important;
        }

        button:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        .run-btn {

            color: white;
            position: relative;
            overflow: hidden;
        }

        .run-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .run-btn:hover::after {
            width: 300px;
            height: 300px;
        }

        .run-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #00e4ff 0%, #00a9ff 100%);
            box-shadow: 0 8px 20px rgba(0, 212, 255, 0.5);
        }

        .run-btn.running {
            animation: pulse 1s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .main-content {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background: var(--bg-card);
            border-right: 2px solid var(--border-color);
            display: flex;
            flex-direction: column;
            animation: slideInLeft 0.3s ease;
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .sidebar-header {
            padding: 16px;
            border-bottom: 1px solid #3a3a52;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-header h3 {
            font-size: 14px;
            color: #a0a0b0;
        }

        .add-file-btn {
            background: transparent;
            border: 1px solid #0099ff;
            padding: 4px 8px;
            font-size: 18px;
            border-radius: 4px;
        }

        .files-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px;
        }

        .file-item {
            padding: 10px 12px;
            margin: 4px 0;
            background: rgba(42, 42, 62, 0.5);
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition-fast);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 3px solid transparent;
            position: relative;
        }

        .file-item::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 6px;
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.2), rgba(102, 126, 234, 0.2));
            opacity: 0;
            transition: var(--transition-fast);
        }

        .file-item:hover {
            background: rgba(0, 153, 255, 0.15);
            transform: translateX(4px);
        }

        .file-item:hover::after {
            opacity: 1;
        }

        .file-item.active {
            background: rgba(0, 153, 255, 0.25);
            border-left-color: var(--primary-color);
            box-shadow: 0 2px 8px rgba(0, 212, 255, 0.2);
        }

        .file-name {
            flex: 1;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .file-icon {
            font-size: 16px;
        }

        .delete-file-btn {
            background: transparent;
            border: none;
            padding: 4px;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .file-item:hover .delete-file-btn {
            opacity: 1;
        }

        .delete-file-btn:hover {
            color: #f87171;
        }

        .editor-section {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .editor-tabs {
            background: black;
            padding: 8px 16px;
            border-bottom: 1px solid #3a3a52;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .editor-controls {
            font-size: larger;
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            margin-left: auto;
        }

        .editor-tab {
            padding: 6px 12px;
            background: var(--primary-color);
            border-radius: 4px;
            font-size: 13px;
            cursor: default;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .split-view {
            flex: 1;
            display: flex;
            overflow: hidden;
            position: relative;
        }

        .editor-container {
            flex: 1;
            position: relative;
            min-width: 300px;
        }

        .split-divider {
            width: 4px;
            background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
            cursor: col-resize;
            user-select: none;
            transition: background 0.2s ease;
            z-index: 50;
            position: relative;
        }

        .split-divider:hover,
        .split-divider.dragging {
            background: var(--primary-color);
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
        }

        .preview-container {
            flex: 1;
            background: white;
            border-left: 2px solid #3a3a52;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-width: 300px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease, flex 0.2s ease;
        }

        .preview-container.show {
            opacity: 1;
            pointer-events: auto;
        }

        .preview-header {
            background: #f0f0f0;
            padding: 10px 16px;
            border-bottom: 1px solid #ddd;
            font-weight: 600;
            font-size: 14px;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .preview-iframe {
            width: 100%;
            height: calc(100% - 41px);
            border: none;
            background: white;
        }

        #editor {
            width: 100%;
            height: 100%;
        }

        .output-container {
            height: 200px;
            background: var(--bg-darker);
            display: flex;
            flex-direction: column;
            border-top: 2px solid var(--border-color);
            position: relative;
        }

        .output-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .output-header {
            background: rgba(42, 42, 62, 0.8);
            padding: 10px 16px;
            border-bottom: 1px solid #3a3a52;
            font-weight: 600;
            font-size: 14px;
            color: #a0a0b0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .clear-output {
            background: transparent;
            border: 1px solid #555;
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 4px;
        }

        #output {
            flex: 1;
            color: #4ade80;
            padding: 16px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            line-height: 1.6;
            font-size: 13px;
        }

        .error {
            color: #f87171;
        }

        .info {
            color: #60a5fa;
        }

        .loading {
            color: #facc15;
        }

        .success {
            color: #4ade80;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: var(--bg-card);
            padding: 28px;
            border-radius: 16px;
            border: 2px solid var(--secondary-color);
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow-lg);
            animation: modalSlideIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes modalSlideIn {
            from {
                transform: scale(0.8) translateY(-50px);
                opacity: 0;
            }

            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .modal-content h3 {
            margin-bottom: 16px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-content input,
        .modal-content select,
        .modal-content textarea {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            background: rgba(42, 42, 62, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 14px;
            transition: var(--transition-fast);
        }

        .modal-content input:focus,
        .modal-content select:focus,
        .modal-content textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
        }

        .modal-content textarea {
            resize: vertical;
            min-height: 60px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #a0a0b0;
            font-size: 13px;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .modal-buttons button {
            flex: 1;
        }

        .notification {
            position: fixed;
            top: 80px;
            right: 20px;
            background: var(--secondary-color);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            opacity: 0;
            transform: translateX(400px);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 2000;
            max-width: 350px;
            border-left: 4px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
        }

        .notification.show {
            opacity: 1;
            transform: translateX(0);
            animation: notifShake 0.5s ease 0.4s;
        }

        @keyframes notifShake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        .notification.error {
            background: #f87171;
        }

        .notification.success {
            background: #0f9641ff;
        }

        .project-mode-toggle {
            padding: 8px 14px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .project-mode-toggle:hover:not(.active) {
            background: var(--primary-color);
        }

        .project-mode-toggle.active {
            background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
            border-color: #22c55e;
            color: white;
            box-shadow: 0 6px 20px rgba(74, 222, 128, 0.4);
        }

        .project-mode-toggle.active:hover {
            background: linear-gradient(135deg, #52e081 0%, #2dd964 100%);
            box-shadow: 0 8px 24px rgba(74, 222, 128, 0.5);
            transform: translateY(-2px);
        }

        .project-mode-toggle:not(.active) {
            opacity: 0.7;
        }

        .project-mode-toggle:not(.active):hover {
            opacity: 1;
        }

        /* Tooltip Styles */
        [data-tooltip] {
            position: relative;
        }

        [data-tooltip]::after {
            content: attr(data-tooltip);
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(8px);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: var(--transition-fast);
            z-index: 1000;
        }

        [data-tooltip]::before {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(2px);
            border: 4px solid transparent;
            border-bottom-color: rgba(0, 0, 0, 0.9);
            opacity: 0;
            pointer-events: none;
            transition: var(--transition-fast);
        }

        [data-tooltip]:hover::after,
        [data-tooltip]:hover::before {
            opacity: 1;
        }

        /* Keyboard Shortcuts Panel */
        .shortcuts-panel {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
            max-width: 300px;
            box-shadow: var(--shadow-lg);
            z-index: 1500;
            opacity: 0;
            transform: translateY(20px);
            transition: var(--transition-normal);
            pointer-events: none;
        }

        .shortcuts-panel.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .shortcuts-panel h4 {
            color: var(--primary-color);
            margin-bottom: 12px;
            font-size: 14px;
        }

        .shortcut-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 13px;
        }

        .shortcut-item:last-child {
            border-bottom: none;
        }

        .shortcut-key {
            background: rgba(0, 153, 255, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 11px;
            border: 1px solid var(--secondary-color);
        }

        .shortcuts-toggle {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition-normal);
            z-index: 1400;
            box-shadow: var(--shadow-md);
        }

        .shortcuts-toggle:hover {
            background: rgba(0, 153, 255, 0.2);
            transform: scale(1.1);
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .share-link {
            background: rgba(0, 153, 255, 0.1);
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #0099ff;
            margin-top: 10px;
            word-break: break-all;
            font-family: monospace;
            font-size: 12px;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 153, 255, 0.3);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 153, 255, 0.5);
        }

        /* Resizer for output panel */
        .resizer {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: transparent;
            cursor: ns-resize;
            z-index: 10;
            transition: var(--transition-fast);
        }

        .resizer:hover,
        .resizer:active {
            background: var(--primary-color);
        }

        @media (max-width: 768px) {
            .editor-tabs {
                flex-direction: column;
                align-items: flex-start;
            }

            .editor-controls {
                width: 100%;
                justify-content: flex-start;
                margin-left: 0;
            }

            header {
                padding: 12px 16px;
            }

            .logo {
                font-size: 18px;
            }

            .logo::before {
                font-size: 22px;
            }

            #controls {
                gap: 6px;
            }

            select,
            button {
                padding: 8px 12px;
                font-size: 12px;
            }

            .output-container {
                height: 120px;
            }

            .sidebar {
                width: 200px;
            }

            .preview-container.show {
                width: 100%;
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 100;
            }

            .shortcuts-toggle {
                width: 40px;
                height: 40px;
                bottom: 16px;
                left: 16px;
            }

            .shortcuts-panel {
                max-width: calc(100vw - 32px);
                bottom: 70px;
                left: 16px;
            }
        }

        /* Snippet Browser Styles */
        .snippets-browser {
            max-height: 400px;
            overflow-y: auto;
            margin: 15px 0;
        }

        .snippets-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .snippet-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .snippet-item:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-color);
            transform: translateX(4px);
        }

        .snippet-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--accent-color);
            border-radius: 8px;
            font-size: 18px;
            color: white;
        }

        .snippet-info {
            flex: 1;
            min-width: 0;
        }

        .snippet-title {
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }

        .snippet-meta {
            display: flex;
            gap: 12px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .snippet-lang {
            background: rgba(0, 212, 255, 0.2);
            color: var(--primary-color);
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 500;
        }

        .snippet-actions {
            display: flex;
            gap: 8px;
        }

        .snippet-delete-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 6px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0;
        }

        .snippet-delete-btn:hover {
            background: rgba(248, 113, 113, 0.2);
            border-color: var(--error-color);
            color: var(--error-color);
        }

        .loading-snippets,
        .no-snippets,
        .error-message {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
        }

        .no-snippets i,
        .error-message i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
            opacity: 0.5;
        }

        .no-snippets p {
            font-size: 16px;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .error-message {
            color: var(--error-color);
        }

        /* Session Info & End Session Button Styles */
        .session-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .session-user {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .session-user i {
            color: var(--success-color);
        }

        .session-user-id {
            color: var(--primary-color);
            font-weight: 600;
        }

        .end-session-btn {
            padding: 6px 12px !important;
            font-size: 12px !important;
            background: transparent !important;
            border: 1px solid var(--error-color) !important;
            color: var(--error-color) !important;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .end-session-btn:hover {
            background: var(--error-color) !important;
            color: white !important;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>
    <div class="main-content">
        <!-- Sidebar for file management -->
        <div class="sidebar" id="sidebar" style="display: none;">
            <div class="sidebar-header">
                <h3>Files</h3>
                <button class="add-file-btn" id="addFileBtn">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="files-list" id="filesList">
                <!-- File items will be added here -->
            </div>
        </div>

        <!-- Editor section -->
        <div class="editor-section">
            <!-- editor header  -->
            <div class="editor-tabs" id="editorTabs">
                <div class="editor-tab">
                    <span id="currentFileName">untitled</span>
                </div>
                <div>
                    <span>Language:
                        <select id="lang-select" data-tooltip="Select programming language">
                            <option value="python">Python</option>
                            <option value="javascript">JavaScript</option>
                            <option value="cpp">C++</option>
                            <option value="java">Java</option>
                            <option value="html">HTML</option>
                            <option value="css">CSS</option>
                        </select>
                    </span>
                </div>
                <div class="editor-controls">
                    <!-- Session Info -->
                    <div class="session-info">
                        <div class="session-user">
                            <i class="fas fa-user-circle"></i>
                            <span>User: <span class="session-user-id"><?php echo htmlspecialchars($currentUserId); ?></span></span>
                        </div>
                        <button class="end-session-btn" id="endSessionBtn" data-tooltip="End session and logout">
                            <i class="fas fa-sign-out-alt"></i> End Session
                        </button>
                    </div>

                    <button id="newBtn" data-tooltip="Create new snippet">
                        <i class="fas fa-plus"></i> New
                    </button>
                    <button id="copyBtn" data-tooltip="Copy to clipboard">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                    <button id="saveBtn" data-tooltip="Save snippet (Ctrl+S)">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button id="loadBtn" data-tooltip="Load snippet">
                        <i class="fas fa-folder-open"></i> Load
                    </button>
                    <button class="project-mode-toggle" id="projectModeBtn" data-tooltip="Toggle multi-file project mode">
                        <i class="fas fa-folder"></i> Web Mode
                    </button>
                    <button class="toggle-preview-btn" id="togglePreviewBtn" style="display: none;" data-tooltip="Toggle live preview">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button class="run-btn" id="runBtn" style="background-color: #0b602aff;" data-tooltip="Run code (Ctrl+Enter)">
                        <i class="fas fa-play"></i> Run
                    </button>
                </div>
            </div>

            <div class="split-view" id="splitView">
                <div class="editor-container">
                    <div id="editor"></div>
                </div>

                <!-- Resizable divider -->
                <div class="split-divider" id="splitDivider"></div>

                <!-- Preview panel for HTML/CSS/JS -->
                <div class="preview-container" id="previewContainer">
                    <div class="preview-header">
                        Live Preview
                        <button class="clear-output" id="refreshPreviewBtn">
                            <i class="fas fa-redo"></i> Refresh
                        </button>
                    </div>
                    <iframe id="previewFrame" class="preview-iframe" sandbox="allow-scripts"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- output console -->
    <div class="output-container">
        <div class="resizer" id="outputResizer"></div>
        <div class="output-header">
            Output Console
            <button class="clear-output" id="clearBtn">
                <i class="fas fa-trash"></i> Clear
            </button>
        </div>
        <div id="output">Ready to run your code...</div>
    </div>

    <!-- Keyboard Shortcuts Toggle -->
    <div class="shortcuts-toggle" id="shortcutsToggle" data-tooltip="Keyboard shortcuts (?)">
        <i class="fas fa-keyboard"></i>
    </div>

    <!-- Keyboard Shortcuts Panel -->
    <div class="shortcuts-panel" id="shortcutsPanel">
        <h4><i class="fas fa-keyboard"></i> Keyboard Shortcuts</h4>
        <div class="shortcut-item">
            <span>Run Code</span>
            <span class="shortcut-key">Ctrl + Enter</span>
        </div>
        <div class="shortcut-item">
            <span>Save Snippet</span>
            <span class="shortcut-key">Ctrl + S</span>
        </div>
        <div class="shortcut-item">
            <span>Toggle Shortcuts</span>
            <span class="shortcut-key">?</span>
        </div>
        <div class="shortcut-item">
            <span>Focus Editor</span>
            <span class="shortcut-key">Ctrl + E</span>
        </div>
        <div class="shortcut-item">
            <span>Toggle Preview</span>
            <span class="shortcut-key">Ctrl + P</span>
        </div>
    </div>

    <!-- Add File Modal -->
    <div class="modal" id="addFileModal">
        <div class="modal-content">
            <h3><i class="fas fa-file"></i> Add New File</h3>
            <div class="form-group">
                <label>File Name *</label>
                <input type="text" id="newFileName" placeholder="e.g., index.html, style.css, script.js">
            </div>
            <div class="modal-buttons">
                <button id="cancelAddFile">Cancel</button>
                <button id="confirmAddFile" class="run-btn">Add File</button>
            </div>
        </div>
    </div>

    <!-- Save Modal -->
    <div class="modal" id="saveModal">
        <div class="modal-content">
            <h3><i class="fas fa-save"></i> Save Code Snippet</h3>
            <div class="form-group">
                <label>Title *</label>
                <input type="text" id="snippetTitle" placeholder="e.g., Hello World Program">
            </div>
            <div class="form-group">
                <label>Description (optional)</label>
                <textarea id="snippetDesc" placeholder="Brief description of your code..."></textarea>
            </div>
            <div class="form-group">
                <label>Visibility</label>
                <select id="snippetPermission">
                    <option value="public">Public - Anyone can view</option>
                    <option value="unlisted">Unlisted - Only with link</option>
                    <option value="private">Private - Only you</option>
                </select>
            </div>
            <div class="modal-buttons">
                <button id="cancelSave">Cancel</button>
                <button id="confirmSave" class="run-btn">Save Snippet</button>
            </div>
        </div>
    </div>

    <!-- Load Modal -->
    <div class="modal" id="loadModal">
        <div class="modal-content" style="max-width: 500px;">
            <h3><i class="fas fa-folder-open"></i> My Snippets</h3>
            <div class="snippets-browser">
                <div class="snippets-list" id="snippetsList">
                    <div class="loading-snippets"><i class="fas fa-spinner fa-spin"></i> Loading your snippets...</div>
                </div>
            </div>
            <div class="modal-buttons">
                <button id="cancelLoad">Close</button>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification"></div>

    <!-- MONACO EDITOR -->
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs/loader.js"></script>

    <script>
        // API Configuration
        const API_BASE = './api';
        const REDIRECT_URL = 'https://j100coders.org/coder/codelab.php';

        // Get user ID from PHP session (injected server-side)
        const currentUserId = '<?php echo htmlspecialchars($currentUserId); ?>';

        // Get snippet ID from PHP (injected server-side) - FIXED: Define this early
        const initialSnippetId = <?php echo $snippetId ? "'" . htmlspecialchars($snippetId) . "'" : 'null'; ?>;

        console.log('Current User ID (from session):', currentUserId);
        console.log('Initial Snippet ID:', initialSnippetId);

        // Session Management
        const SessionManager = {
            // Check if session is valid
            async validate() {
                try {
                    const response = await fetch(`${API_BASE}/session.php`);
                    const data = await response.json();

                    if (!data.authenticated) {
                        this.redirectToLogin();
                        return false;
                    }
                    return true;
                } catch (error) {
                    console.error('Session validation error:', error);
                    return false;
                }
            },

            // End session and redirect
            async endSession() {
                try {
                    const response = await fetch(`${API_BASE}/session.php`, {
                        method: 'DELETE'
                    });
                    const data = await response.json();

                    if (data.success) {
                        showNotification('Session ended. Redirecting...', 'info');
                        setTimeout(() => {
                            window.location.href = data.redirect || REDIRECT_URL;
                        }, 1000);
                    }
                } catch (error) {
                    console.error('End session error:', error);
                    // Redirect anyway
                    window.location.href = REDIRECT_URL;
                }
            },

            // Redirect to login page
            redirectToLogin() {
                showNotification('Session expired. Redirecting to login...', 'warning');
                setTimeout(() => {
                    window.location.href = REDIRECT_URL;
                }, 1500);
            },

            // Get current user ID
            getUserId() {
                return currentUserId;
            }
        };

        // End Session Button Handler
        document.getElementById('endSessionBtn').addEventListener('click', async () => {
            if (confirm('Are you sure you want to end your session?\n\nAny unsaved changes will be lost.')) {
                await SessionManager.endSession();
            }
        });

        // Clean URL after page load (remove ?as= parameter but keep ?snippet=)
        window.addEventListener('load', () => {
            const url = new URL(window.location.href);
            const snippetParam = url.searchParams.get('snippet');

            // Remove 'as' parameter from URL (already stored in session)
            if (url.searchParams.has('as')) {
                url.searchParams.delete('as');

                // Keep snippet parameter if present
                if (snippetParam) {
                    url.searchParams.set('snippet', snippetParam);
                }

                // Update URL without reload
                window.history.replaceState({}, '', url.pathname + (snippetParam ? `?snippet=${snippetParam}` : ''));
            }
        });

        // Validate session periodically (every 5 minutes)
        setInterval(() => {
            SessionManager.validate();
        }, 5 * 60 * 1000);

        // Default code templates
        const templates = {
            python: `# Python Code\nprint("Hello from J100Coders CodeBin!")`,
            javascript: `// JavaScript Code\nconsole.log("Hello from J100Coders CodeBin!");`,
            cpp: `#include <iostream>\nusing namespace std;\n\nint main() {\n    cout << "Hello from J100Coders!" << endl;\n    return 0;\n}`,
            java: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Hello from J100Coders!");\n    }\n}`,
            html: `<!DOCTYPE html>\n<html>\n<head>\n    <title>J100Coders</title>\n</head>\n<body>\n    <h1>Hello from J100Coders CodeBin!</h1>\n</body>\n</html>`,
            css: `/* CSS Code */\nbody {\n    background: #0f0f23;\n    color: white;\n    font-family: Arial, sans-serif;\n}`,
            sql: `-- SQL Query\nSELECT * FROM users\nWHERE created_at >= CURRENT_DATE - INTERVAL '7 days';`,
        };

        // File Management State
        let projectMode = false;
        let files = [];
        let currentFileIndex = 0;

        // Language switch - FIXED: No reload, proper editor update
        document.getElementById("lang-select").addEventListener("change", function() {
            if (!editor) return;
            const lang = this.value;

            // Update Monaco editor language
            monaco.editor.setModelLanguage(editor.getModel(), lang);

            // If empty, set template
            if (!editor.getValue().trim()) {
                editor.setValue(templates[lang]);
            }

            editor.setValue(templates[lang]);

            // Update current file if in project mode
            if (projectMode && files.length > 0) {
                files[currentFileIndex].language = lang;
                files[currentFileIndex].content = templates[lang];
            }

            // Show/hide preview for HTML/CSS/JS
            updatePreviewVisibility();

            showNotification(`Switched to ${lang.toUpperCase()}`, 'success');
        });

        // Load Monaco
        require.config({
            paths: {
                'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs'
            }
        });

        let editor = null;
        let currentSnippetId = null;
        let editorReady = false;

        // Helper functions - Define BEFORE Monaco loads
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            if (!dateString) return 'Unknown';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        function getLanguageIcon(lang) {
            const icons = {
                'python': 'snake',
                'javascript': 'js',
                'java': 'coffee',
                'cpp': 'code',
                'c': 'code',
                'html': 'html5',
                'css': 'css3-alt',
                'sql': 'database',
                'php': 'php',
                'ruby': 'gem',
                'go': 'code',
                'rust': 'cog',
                'typescript': 'code'
            };
            return icons[lang] || 'file-code';
        }

        // Show notification - Define early
        function showNotification(message, type = 'info') {
            const icons = {
                'success': '<i class="fas fa-check-circle"></i>',
                'error': '<i class="fas fa-exclamation-circle"></i>',
                'warning': '<i class="fas fa-exclamation-triangle"></i>',
                'info': '<i class="fas fa-info-circle"></i>'
            };
            const icon = icons[type] || '<i class="fas fa-info-circle"></i>';

            const notif = document.getElementById('notification');
            notif.innerHTML = `${icon} ${message}`;
            notif.className = 'notification show ' + type;
            setTimeout(() => notif.classList.remove('show'), 3000);
        }

        // Load snippet function - Define before Monaco
        async function loadSnippet(snippetId) {
            if (!editor) {
                console.error('Editor not ready, retrying in 500ms...');
                setTimeout(() => loadSnippet(snippetId), 500);
                return;
            }

            const outputDiv = document.getElementById("output");
            outputDiv.innerHTML = '<span class="loading"><i class="fas fa-spinner fa-spin"></i> Loading snippet...</span>';

            try {
                const response = await fetch(`${API_BASE}/snippets/${snippetId}`, {
                    credentials: 'include'
                });

                if (!response.ok) {
                    throw new Error('Snippet not found');
                }

                const snippet = await response.json();

                // Set the editor content
                editor.setValue(snippet.code);

                // Set the language
                const langSelect = document.getElementById('lang-select');
                if (langSelect.querySelector(`option[value="${snippet.language}"]`)) {
                    langSelect.value = snippet.language;
                    monaco.editor.setModelLanguage(editor.getModel(), snippet.language);
                }

                currentSnippetId = snippet.id;

                outputDiv.innerHTML =
                    `<span class="success"><i class="fas fa-check-circle"></i> Snippet loaded successfully!</span>\n\n` +
                    `Title: ${escapeHtml(snippet.title)}\n` +
                    `Language: ${snippet.language}\n` +
                    `Author: ${snippet.author_id}\n` +
                    `Created: ${new Date(snippet.created_at).toLocaleString()}\n` +
                    (snippet.description ? `\nDescription: ${escapeHtml(snippet.description)}` : '');

                showNotification('Snippet loaded!', 'success');

            } catch (error) {
                console.error('Load error:', error);
                outputDiv.innerHTML = '<span class="error"><i class="fas fa-exclamation-circle"></i> Failed to load snippet. Check the ID and try again.</span>';
                showNotification('Failed to load snippet', 'error');
            }
        }

        // Initialize Monaco Editor
        require(["vs/editor/editor.main"], function() {
            try {
                console.log('Monaco Editor loading...');

                editor = monaco.editor.create(document.getElementById("editor"), {
                    value: templates[document.getElementById("lang-select").value],
                    language: document.getElementById("lang-select").value,
                    theme: "vs-dark",
                    automaticLayout: true,
                    fontSize: 14,
                    minimap: {
                        enabled: true
                    },
                    scrollBeyondLastLine: false,
                    lineNumbers: 'on',
                    roundedSelection: false,
                    cursorStyle: 'line',
                    wordWrap: 'on',
                    readOnly: false,
                    domReadOnly: false
                });

                // Initialize with single file
                files = [{
                    name: 'untitled',
                    language: document.getElementById("lang-select").value,
                    content: templates[document.getElementById("lang-select").value]
                }];

                editorReady = true;
                console.log('Monaco Editor initialized successfully');

                setTimeout(() => {
                    editor.focus();

                    // FIXED: Load snippet if ID is provided (using PHP-injected value)
                    if (initialSnippetId) {
                        loadSnippet(initialSnippetId);
                    }

                    // Setup auto-update for preview
                    setupPreviewAutoUpdate();
                }, 100);

            } catch (error) {
                console.error('Monaco Editor initialization error:', error);
                document.getElementById('output').innerHTML =
                    `<span class="error"><i class="fas fa-exclamation-circle"></i> Error loading editor: ${error.message}</span>\n` +
                    '<span class="info">Please refresh the page or check browser console for details.</span>';
            }
        });

        // Show notification
        function showNotification(message, type = 'info') {
            const icons = {
                'success': '<i class="fas fa-check-circle"></i>',
                'error': '<i class="fas fa-exclamation-circle"></i>',
                'warning': '<i class="fas fa-exclamation-triangle"></i>',
                'info': '<i class="fas fa-info-circle"></i>'
            };
            const icon = icons[type] || '<i class="fas fa-info-circle"></i>';

            const notif = document.getElementById('notification');
            notif.innerHTML = `${icon} ${message}`;
            notif.className = 'notification show ' + type;
            setTimeout(() => notif.classList.remove('show'), 3000);
        }

        // File icon helper
        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const icons = {
                'html': '<i class="fas fa-code"></i>',
                'css': '<i class="fas fa-palette"></i>',
                'js': '<i class="fas fa-bolt"></i>',
                'json': '<i class="fas fa-list"></i>',
                'md': '<i class="fas fa-file-alt"></i>',
                'py': '<i class="fas fa-snake"></i>',
                'java': '<i class="fas fa-cup"></i>',
                'cpp': '<i class="fas fa-gear"></i>',
            };
            return icons[ext] || '<i class="fas fa-file"></i>';
        }

        // Get language from filename
        function getLanguageFromFilename(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const langMap = {
                'html': 'html',
                'css': 'css',
                'js': 'javascript',
                'json': 'json',
                'md': 'markdown',
                'py': 'python',
                'java': 'java',
                'cpp': 'cpp',
                'c': 'cpp',
                'ts': 'typescript'
            };
            return langMap[ext] || 'plaintext';
        }

        // Update preview visibility
        function updatePreviewVisibility() {
            const toggleBtn = document.getElementById('togglePreviewBtn');
            const hasWebFiles = files.some(f => ['html', 'css', 'javascript'].includes(f.language));

            if (projectMode && hasWebFiles) {
                toggleBtn.style.display = 'block';
            } else {
                toggleBtn.style.display = 'none';
                document.getElementById('previewContainer').classList.remove('show');
                toggleBtn.classList.remove('active');
            }
        }

        // Render files list
        function renderFilesList() {
            const filesList = document.getElementById('filesList');
            filesList.innerHTML = '';

            files.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item' + (index === currentFileIndex ? ' active' : '');
                // In renderFilesList function, replace the fileItem.innerHTML section:
                fileItem.innerHTML = `

    <div class="file-name">
        <span class="file-icon">${getFileIcon(file.name)}</span>
        <span>${file.name}</span>
    </div>
    ${files.length > 1 ? `<button class="delete-file-btn" data-index="${index}"><i class="fas fa-times"></i></button>` : ''}

`;

                fileItem.addEventListener('click', (e) => {
                    if (!e.target.classList.contains('delete-file-btn')) {
                        switchToFile(index);
                    }
                });

                filesList.appendChild(fileItem);
            });

            // Add delete listeners
            filesList.querySelectorAll('.delete-file-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    deleteFile(parseInt(btn.dataset.index));
                });
            });
        }

        // Switch to file
        function switchToFile(index) {
            if (!editor) return;

            // Save current file content
            files[currentFileIndex].content = editor.getValue();

            // Switch to new file
            currentFileIndex = index;
            const file = files[currentFileIndex];

            editor.setValue(file.content);
            monaco.editor.setModelLanguage(editor.getModel(), file.language);
            document.getElementById('lang-select').value = file.language;
            document.getElementById('currentFileName').textContent = file.name;

            renderFilesList();
            updatePreview();
        }

        // Delete file
        function deleteFile(index) {
            if (files.length === 1) {
                showNotification('Cannot delete the last file', 'error');
                return;
            }

            if (!confirm(`Delete ${files[index].name}?`)) return;

            files.splice(index, 1);

            if (currentFileIndex >= files.length) {
                currentFileIndex = files.length - 1;
            }

            switchToFile(currentFileIndex);
        }

        // Update preview
        function updatePreview() {
            if (!projectMode) return;

            const previewContainer = document.getElementById('previewContainer');
            if (!previewContainer.classList.contains('show')) return;

            const htmlFile = files.find(f => f.language === 'html');
            if (!htmlFile) {
                const iframe = document.getElementById('previewFrame');
                iframe.srcdoc = '<h2 style="color: #f87171; padding: 20px;"><i class="fas fa-exclamation-circle"></i> No HTML file found in project</h2>';
                return;
            }

            const cssFile = files.find(f => f.language === 'css');
            const jsFile = files.find(f => f.language === 'javascript');

            let html = htmlFile.content;

            // Inject CSS
            if (cssFile && cssFile.content.trim()) {
                const styleTag = `<style>\n${cssFile.content}\n</style>`;
                if (html.includes('</head>')) {
                    html = html.replace('</head>', `${styleTag}\n</head>`);
                } else if (html.includes('<head>')) {
                    html = html.replace('<head>', `<head>\n${styleTag}`);
                } else {
                    html = styleTag + '\n' + html;
                }
            }

            // Inject JS - FIXED: Properly escape closing script tags
            if (jsFile && jsFile.content.trim()) {
                // Escape any closing script tags in the JS code
                const escapedJS = jsFile.content.replace(/<\/script>/gi, '<\\/script>');
                const scriptTag = `<script>\n${escapedJS}\n<\\/script>`;

                if (html.includes('</body>')) {
                    html = html.replace('</body>', `${scriptTag}\n</body>`);
                } else {
                    html = html + '\n' + scriptTag;
                }
            }

            const iframe = document.getElementById('previewFrame');
            try {
                iframe.srcdoc = html;
                console.log('Preview updated successfully');
            } catch (error) {
                console.error('Preview error:', error);
                iframe.srcdoc = `<h2 style="color: #f87171; padding: 20px;">Error rendering preview</h2>
<pre>${error.message}</pre>`;
            }
        }

        // Project Mode Toggle
        document.getElementById('projectModeBtn').addEventListener('click', function() {
            projectMode = !projectMode;
            const sidebar = document.getElementById('sidebar');
            const langSelect = document.getElementById('lang-select');

            if (projectMode) {
                this.classList.add('active');
                sidebar.style.display = 'flex';
                langSelect.disabled = true;

                // Initialize with a fresh HTML project structure
                files = [{
                    name: 'index.html',
                    language: 'html',
                    content: `<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Project</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Welcome to J100 Code SandBox</h1>
    <script src="script.js"><\\/script>
</body>

</html>`
                }, {
                    name: 'style.css',
                    language: 'css',
                    content: `/* CSS Stylesheet */
* {
margin: 0;
padding: 0;
box-sizing: border-box;
}

body {
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
color: #fff;
min-height: 100vh;
justify-content: center;
align-items: center;
}

h1 {
font-size: 48px;
color: white;
text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}`
                }, {
                    name: 'script.js',
                    language: 'javascript',
                    content: `// JavaScript File
console.log('Project initialized!');

document.addEventListener('DOMContentLoaded', function() {
console.log('DOM loaded and parsed');
// Add your code here
});`
                }];

                currentFileIndex = 0;

                // Load the first file into editor
                if (editor) {
                    editor.setValue(files[0].content);
                    monaco.editor.setModelLanguage(editor.getModel(), files[0].language);
                    langSelect.value = files[0].language;
                    document.getElementById('currentFileName').textContent = files[0].name;
                }

                renderFilesList();
                updatePreviewVisibility();
                showNotification('✓ Project mode enabled - VS Code style', 'success');
            } else {
                this.classList.remove('active');
                sidebar.style.display = 'none';
                langSelect.disabled = false;
                document.getElementById('previewContainer').classList.remove('show');
                document.getElementById('togglePreviewBtn').classList.remove('active');

                // Reset to single file mode
                files = [{
                    name: 'untitled',
                    language: langSelect.value,
                    content: editor.getValue()
                }];
                currentFileIndex = 0;

                updatePreviewVisibility();
                showNotification('Project mode disabled', 'info');
            }
        });

        // Add File Button
        document.getElementById('addFileBtn').addEventListener('click', () => {
            document.getElementById('addFileModal').classList.add('show');
            document.getElementById('newFileName').focus();
        });

        document.getElementById('cancelAddFile').addEventListener('click', () => {
            document.getElementById('addFileModal').classList.remove('show');
            document.getElementById('newFileName').value = '';
        });

        document.getElementById('confirmAddFile').addEventListener('click', () => {
            const filename = document.getElementById('newFileName').value.trim();

            if (!filename) {
                showNotification('Please enter a filename', 'error');
                return;
            }

            if (files.some(f => f.name === filename)) {
                showNotification('File already exists', 'error');
                return;
            }

            const language = getLanguageFromFilename(filename);
            const newFile = {
                name: filename,
                language: language,
                content: templates[language] || ''
            };

            files.push(newFile);
            switchToFile(files.length - 1);

            document.getElementById('addFileModal').classList.remove('show');
            document.getElementById('newFileName').value = '';

            updatePreviewVisibility();
            showNotification(`Added ${filename}`, 'success');
        });

        // Toggle Preview
        document.getElementById('togglePreviewBtn').addEventListener('click', function() {
            const previewContainer = document.getElementById('previewContainer');
            previewContainer.classList.toggle('show');
            this.classList.toggle('active');

            if (previewContainer.classList.contains('show')) {
                updatePreview();
            }
        });

        // Refresh Preview
        document.getElementById('refreshPreviewBtn').addEventListener('click', () => {
            files[currentFileIndex].content = editor.getValue();
            updatePreview();
            showNotification('Preview refreshed', 'success');
        });

        // Auto-update preview on editor change (debounced)
        let previewTimeout;

        function setupPreviewAutoUpdate() {
            if (!editor) return;
            editor.onDidChangeModelContent(() => {
                if (!projectMode) return;

                const previewContainer = document.getElementById('previewContainer');
                if (!previewContainer.classList.contains('show')) return;

                clearTimeout(previewTimeout);
                previewTimeout = setTimeout(() => {
                    files[currentFileIndex].content = editor.getValue();
                    updatePreview();
                }, 500); // ✅ Faster response
            });
        }

        // Call this after editor is ready
        setTimeout(() => {
            if (editorReady) {
                setupPreviewAutoUpdate();
            }
        }, 500);

        // Keyboard Shortcuts Panel
        const shortcutsToggle = document.getElementById('shortcutsToggle');
        const shortcutsPanel = document.getElementById('shortcutsPanel');
        let shortcutsVisible = false;

        shortcutsToggle.addEventListener('click', () => {
            shortcutsVisible = !shortcutsVisible;
            shortcutsPanel.classList.toggle('show', shortcutsVisible);
        });

        // Click outside to close shortcuts panel
        document.addEventListener('click', (e) => {
            if (shortcutsVisible && !shortcutsPanel.contains(e.target) && !shortcutsToggle.contains(e.target)) {
                shortcutsVisible = false;
                shortcutsPanel.classList.remove('show');
            }
        });

        // Output Panel Resizer
        const outputResizer = document.getElementById('outputResizer');
        const outputContainer = document.querySelector('.output-container');
        let isResizing = false;

        outputResizer.addEventListener('mousedown', (e) => {
            isResizing = true;
            document.body.style.cursor = 'ns-resize';
            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isResizing) return;

            const containerRect = outputContainer.parentElement.getBoundingClientRect();
            const newHeight = containerRect.bottom - e.clientY;

            if (newHeight >= 100 && newHeight <= 500) {
                outputContainer.style.height = newHeight + 'px';
            }
        });

        document.addEventListener('mouseup', () => {
            if (isResizing) {
                isResizing = false;
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
            }
        });

        // Enhanced notification with icons
        showNotification = function(message, type = 'info') {
            const icons = {
                'success': '<i class="fas fa-check-circle"></i>',
                'error': '<i class="fas fa-exclamation-circle"></i>',
                'warning': '<i class="fas fa-exclamation-triangle"></i>',
                'info': '<i class="fas fa-info-circle"></i>'
            };
            const icon = icons[type] || '<i class="fas fa-info-circle"></i>';

            const notif = document.getElementById('notification');
            notif.innerHTML = `${icon} ${message}`;
            notif.className = 'notification show ' + type;
            setTimeout(() => notif.classList.remove('show'), 3000);
        };

        // Loading state helper
        function setLoading(button, isLoading) {
            if (isLoading) {
                button.dataset.originalText = button.textContent;
                button.innerHTML = '<span class="spinner"></span> Loading...';
                button.disabled = true;
            } else {
                button.textContent = button.dataset.originalText || button.textContent;
                button.disabled = false;
            }
        }


        // Copy button
        document.getElementById("copyBtn").onclick = () => {
            if (!editor) {
                showNotification("Editor not ready!", "error");
                return;
            }
            const code = editor.getValue();
            navigator.clipboard.writeText(code);
            showNotification("✓ Code copied to clipboard!", "success");
        };

        // Clear output
        document.getElementById("clearBtn").onclick = () => {
            document.getElementById("output").textContent = "Output cleared. Ready to run...";
        };

        // New snippet
        document.getElementById("newBtn").onclick = () => {
            if (editor.getValue().trim() && !confirm('Are you sure? Unsaved changes will be lost.')) {
                return;
            }

            const lang = document.getElementById("lang-select").value;
            const template = templates[lang] || '';

            if (projectMode) {
                // Reset to single file in project mode
                files = [{
                    name: 'index.' + (lang === 'javascript' ? 'js' : lang),
                    language: lang,
                    content: template
                }];
                currentFileIndex = 0;
                renderFilesList();
            }

            editor.setValue(template);
            currentSnippetId = null;
            document.getElementById("output").textContent = "New snippet created. Ready to run...";
            window.history.pushState({}, '', window.location.pathname);

            // Hide preview
            document.getElementById('previewContainer').classList.remove('show');
            document.getElementById('togglePreviewBtn').classList.remove('active');
        };

        // Run Code - Execute via backend API
        document.getElementById("runBtn").onclick = async () => {
            // Save current file content
            if (projectMode && files.length > 0) {
                files[currentFileIndex].content = editor.getValue();
            }

            // If web preview is available, just update it
            const hasHtml = projectMode && files.some(f => f.language === 'html');
            if (hasHtml) {
                updatePreview();

                // Also show preview if not already visible
                const previewContainer = document.getElementById('previewContainer');
                if (!previewContainer.classList.contains('show')) {
                    previewContainer.classList.add('show');
                    document.getElementById('togglePreviewBtn').classList.add('active');
                }

                document.getElementById("output").innerHTML =
                    '<span class="success"><i class="fas fa-check-circle"></i> Web preview updated!</span>\n' +
                    '<span class="info"><i class="fas fa-arrow-right"></i> View the live preview on the right →</span>';
                return;
            }

            const lang = document.getElementById("lang-select").value;
            const code = editor.getValue();
            const outputDiv = document.getElementById("output");
            const runBtn = document.getElementById("runBtn");

            if (!code.trim()) {
                outputDiv.innerHTML = '<span class="error"><i class="fas fa-exclamation-circle"></i> Error: No code to run!</span>';
                return;
            }

            // Show loading state
            runBtn.classList.add('running');
            runBtn.disabled = true;
            outputDiv.innerHTML = '<span class="loading"><i class="fas fa-spinner fa-spin"></i> Executing code...</span>';

            try {
                const response = await fetch(`${API_BASE}/execute.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        language: lang,
                        code: code
                    })
                });

                const result = await response.json();

                if (result.error) {
                    outputDiv.innerHTML = `<span class="info">═══ Execution Error ═══</span>\n<span class="error">${escapeHtml(result.error)}</span>${result.details ? '\n<span class="error">' + escapeHtml(result.details) + '</span>' : ''}`;
                } else {
                    const statusIcon = result.success ?
                        '<i class="fas fa-check-circle"></i>' :
                        '<i class="fas fa-exclamation-triangle"></i>';
                    const statusClass = result.success ? 'success' : 'error';
                    const versionInfo = result.version ? ` (${result.language} ${result.version})` : '';
                    const timeInfo = result.execution_time ? `\n<span class="info">Execution time: ${result.execution_time}s</span>` : '';

                    outputDiv.innerHTML =
                        `<span class="info">═══ ${lang.toUpperCase()}${versionInfo} ═══</span>\n` +
                        `<span class="${statusClass}">${escapeHtml(result.output)}</span>` +
                        timeInfo +
                        `\n<span class="info">═══ ${statusIcon} Completed ═══</span>`;
                }
            } catch (error) {
                console.error('Execution error:', error);
                outputDiv.innerHTML =
                    '<span class="info">═══ Execution Error ═══</span>\n' +
                    '<span class="error"><i class="fas fa-exclamation-circle"></i> Failed to connect to execution service.</span>\n' +
                    '<span class="info">Check your internet connection and try again.</span>';
            } finally {
                runBtn.classList.remove('running');
                runBtn.disabled = false;
            }
        };

        // Save Modal
        document.getElementById("saveBtn").onclick = () => {
            document.getElementById('saveModal').classList.add('show');
            document.getElementById('snippetTitle').focus();
        };

        document.getElementById("cancelSave").onclick = () => {
            document.getElementById('saveModal').classList.remove('show');
            document.getElementById('snippetTitle').value = '';
            document.getElementById('snippetDesc').value = '';
        };

        // Update the confirmSave click handler to handle authentication errors

        document.getElementById("confirmSave").onclick = async () => {
            const title = document.getElementById('snippetTitle').value.trim();
            if (!title) {
                showNotification('Please enter a title!', 'error');
                return;
            }

            const saveBtn = document.getElementById('confirmSave');
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            const snippetData = {
                title: title,
                description: document.getElementById('snippetDesc').value.trim(),
                language: document.getElementById("lang-select").value,
                code: editor.getValue(),
                permissions: document.getElementById('snippetPermission').value,
                author_id: SessionManager.getUserId()

            };

            try {
                const response = await fetch(`${API_BASE}/snippets`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include', // Include session cookies
                    body: JSON.stringify(snippetData)
                });

                const result = await response.json();

                // Handle authentication errors
                if (response.status === 401 || result.redirect) {
                    showNotification('Session expired. Please login again.', 'error');
                    setTimeout(() => {
                        window.location.href = result.redirect || REDIRECT_URL;
                    }, 1500);
                    return;
                }

                if (!response.ok) {
                    throw new Error(result.error || 'Failed to save snippet');
                }

                currentSnippetId = result.id;

                document.getElementById('saveModal').classList.remove('show');
                document.getElementById('snippetTitle').value = '';
                document.getElementById('snippetDesc').value = '';

                const shareUrl = `${window.location.origin}${window.location.pathname}?snippet=${result.id}`;
                window.history.pushState({}, '', `?snippet=${result.id}`);

                showNotification('Snippet saved successfully!', "success");

                document.getElementById("output").innerHTML =
                    `<span class="success"><i class="fas fa-check-circle"></i> Snippet saved successfully!</span>\n\n` +
                    `Title: ${title}\n` +
                    `Language: ${snippetData.language}\n` +
                    `Snippet ID: ${result.id}\n` +
                    `Visibility: ${snippetData.permissions}\n\n` +
                    `<span class="info">Shareable URL:</span>\n` +
                    `<div class="share-link">${shareUrl}</div>`;

            } catch (error) {
                console.error('Save error:', error);
                showNotification('Failed to save snippet. Please try again.', 'error');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Snippet';
            }
        };

        // Update loadUserSnippets to use session
        async function loadUserSnippets() {
            const listContainer = document.getElementById('snippetsList');
            listContainer.innerHTML = '<div class="loading-snippets"><i class="fas fa-spinner fa-spin"></i> Loading your snippets...</div>';

            try {
                const response = await fetch(`${API_BASE}/snippets`, {
                    credentials: 'include' // Include session cookies
                });

                // Handle authentication errors
                if (response.status === 401) {
                    SessionManager.redirectToLogin();
                    return;
                }

                const data = await response.json();

                if (data.snippets && data.snippets.length > 0) {
                    listContainer.innerHTML = data.snippets.map(snippet => `
                        <div class="snippet-item" data-id="${snippet.id}">
                            <div class="snippet-icon">
                                <i class="fas fa-${getLanguageIcon(snippet.language)}"></i>
                            </div>
                            <div class="snippet-info">
                                <div class="snippet-title">${escapeHtml(snippet.title)}</div>
                                <div class="snippet-meta">
                                    <span class="snippet-lang">${snippet.language}</span>
                                    <span class="snippet-date">${formatDate(snippet.created_at)}</span>
                                    <span class="snippet-views"><i class="fas fa-eye"></i> ${snippet.views || 0}</span>
                                </div>
                            </div>
                            <div class="snippet-actions">
                                <button class="snippet-delete-btn" data-id="${snippet.id}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `).join('');

                    // Add click handlers for loading snippets
                    listContainer.querySelectorAll('.snippet-item').forEach(item => {
                        item.addEventListener('click', async (e) => {
                            if (e.target.closest('.snippet-delete-btn')) return;
                            const snippetId = item.dataset.id;
                            document.getElementById('loadModal').classList.remove('show');
                            await loadSnippet(snippetId);
                        });
                    });

                    // Add delete handlers
                    listContainer.querySelectorAll('.snippet-delete-btn').forEach(btn => {
                        btn.addEventListener('click', async (e) => {
                            e.stopPropagation();
                            if (confirm('Delete this snippet?')) {
                                await deleteSnippet(btn.dataset.id);
                                await loadUserSnippets();
                            }
                        });
                    });
                } else {
                    listContainer.innerHTML = `
                        <div class="no-snippets">
                            <i class="fas fa-folder-open"></i>
                            <p>No snippets yet</p>
                            <small>Save your first snippet to see it here!</small>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading snippets:', error);
                listContainer.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-circle"></i> Failed to load snippets</div>';
            }
        }

        // Update deleteSnippet to use session
        async function deleteSnippet(snippetId) {
            try {
                const response = await fetch(`${API_BASE}/snippets/${snippetId}`, {
                    method: 'DELETE',
                    credentials: 'include'
                });

                if (response.status === 401) {
                    SessionManager.redirectToLogin();
                    return;
                }

                if (response.status === 403) {
                    showNotification('You can only delete your own snippets', 'error');
                    return;
                }

                if (response.ok) {
                    showNotification('Snippet deleted', 'success');
                } else {
                    showNotification('Failed to delete snippet', 'error');
                }
            } catch (error) {
                showNotification('Failed to delete snippet', 'error');
            }
        }

        // Load snippet function
        async function loadSnippet(snippetId) {
            if (!editor) {
                console.error('Editor not ready, retrying in 500ms...');
                setTimeout(() => loadSnippet(snippetId), 500);
                return;
            }

            const outputDiv = document.getElementById("output");
            outputDiv.innerHTML = '<span class="loading"><i class="fas fa-spinner fa-spin"></i> Loading snippet...</span>';

            try {
                const response = await fetch(`${API_BASE}/snippets/${snippetId}`, {
                    credentials: 'include'
                });

                if (!response.ok) {
                    throw new Error('Snippet not found');
                }

                const snippet = await response.json();

                // Set the editor content
                editor.setValue(snippet.code);

                // Set the language
                const langSelect = document.getElementById('lang-select');
                if (langSelect.querySelector(`option[value="${snippet.language}"]`)) {
                    langSelect.value = snippet.language;
                    monaco.editor.setModelLanguage(editor.getModel(), snippet.language);
                }

                currentSnippetId = snippet.id;

                outputDiv.innerHTML =
                    `<span class="success"><i class="fas fa-check-circle"></i> Snippet loaded successfully!</span>\n\n` +
                    `Title: ${escapeHtml(snippet.title)}\n` +
                    `Language: ${snippet.language}\n` +
                    `Author: ${snippet.author_id}\n` +
                    `Created: ${new Date(snippet.created_at).toLocaleString()}\n` +
                    (snippet.description ? `\nDescription: ${escapeHtml(snippet.description)}` : '');

                showNotification('Snippet loaded!', 'success');

            } catch (error) {
                console.error('Load error:', error);
                outputDiv.innerHTML = '<span class="error"><i class="fas fa-exclamation-circle"></i> Failed to load snippet. Check the ID and try again.</span>';
                showNotification('Failed to load snippet', 'error');
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl+S - Save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.getElementById('saveBtn').click();
            }
            // Ctrl+Enter - Run
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('runBtn').click();
            }
            // ? - Toggle shortcuts panel
            if (e.key === '?' && !e.ctrlKey && !e.metaKey) {
                if (!['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                    e.preventDefault();
                    shortcutsToggle.click();
                }
            }
            // Ctrl+E - Focus editor
            if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                e.preventDefault();
                if (editor) editor.focus();
            }
            // Ctrl+P - Toggle preview
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                const previewBtn = document.getElementById('togglePreviewBtn');
                if (previewBtn.style.display !== 'none') {
                    previewBtn.click();
                }
            }
            // Escape - Close modals
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.show').forEach(modal => {
                    modal.classList.remove('show');
                });
                if (shortcutsVisible) {
                    shortcutsPanel.classList.remove('show');
                    shortcutsVisible = false;
                }
            }
        });

        // Add fade-in animation to main content
        window.addEventListener('load', () => {
            document.querySelector('.main-content').style.animation = 'fadeIn 0.5s ease';
        });

        const fadeInKeyframes = `
        @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
        }
        `;
        const styleSheet = document.createElement('style');
        styleSheet.textContent = fadeInKeyframes;
        document.head.appendChild(styleSheet);
    </script>

</body>

</html>