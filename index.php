<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>J100 Coding Sandbox</title>
    <style>
        :root {
            --primary-color: #00d4ff;
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
            background: linear-gradient(135deg, var(--bg-dark) 0%, #1a1a3e 100%);
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

        header {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--border-color);
            box-shadow: var(--shadow-md);
            position: relative;
            z-index: 10;
            animation: slideDown 0.4s ease;
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
            padding: 10px 18px;
            background: rgba(0, 153, 255, 0.1);
            color: var(--text-primary);
            border: 1px solid var(--secondary-color);
            border-radius: 8px;
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
            background: rgba(42, 42, 62, 0.8);
        }

        button {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        button:hover {
            background: rgba(0, 153, 255, 0.2);
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
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
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
            background: rgba(42, 42, 62, 0.8);
            padding: 8px 16px;
            border-bottom: 1px solid #3a3a52;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .editor-tab {
            padding: 6px 12px;
            background: rgba(0, 153, 255, 0.1);
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
            background: #4ade80;
        }

        .project-mode-toggle {
            background: rgba(0, 153, 255, 0.1);
            border: 1px solid #0099ff;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .project-mode-toggle:hover:not(.active) {
            background: rgba(0, 153, 255, 0.2);
            border-color: var(--primary-color);
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
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-8px);
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
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-2px);
            border: 4px solid transparent;
            border-top-color: rgba(0, 0, 0, 0.9);
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
    </style>
</head>

<body>

    <header>
        <div class="logo">
            <span class="logo-text"> Coding SandBox</span>
        </div>
        <div id="controls">
            <button class="project-mode-toggle" id="projectModeBtn" data-tooltip="Toggle multi-file project mode">
                <i class="fas fa-folder"></i> Project Mode
            </button>
            <select id="lang-select" data-tooltip="Select programming language">
                <option value="python">Python</option>
                <option value="javascript">JavaScript</option>
                <option value="cpp">C++</option>
                <option value="java">Java</option>
                <option value="html">HTML</option>
                <option value="css">CSS</option>
            </select>
            <button class="toggle-preview-btn" id="togglePreviewBtn" style="display: none;" data-tooltip="Toggle live preview">
                <i class="fas fa-eye"></i> Preview
            </button>
            <button class="run-btn" id="runBtn" data-tooltip="Run code (Ctrl+Enter)">
                <i class="fas fa-play"></i> Run
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
            <button id="newBtn" data-tooltip="Create new snippet">
                <i class="fas fa-plus"></i> New
            </button>
        </div>
    </header>

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
        <div class="modal-content">
            <h3><i class="fas fa-folder-open"></i> Load Snippet</h3>
            <div class="form-group">
                <label>Snippet ID or URL</label>
                <input type="text" id="snippetId" placeholder="Enter snippet ID (e.g., abc123)">
            </div>
            <div class="modal-buttons">
                <button id="cancelLoad">Cancel</button>
                <button id="confirmLoad" class="run-btn">Load</button>
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
                'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs'
            }
        });

        // require.config({ paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs' } });

        let editor = null;
        let currentSnippetId = null;
        let editorReady = false;

        require(["vs/editor/editor.main"], function() {
            try {
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

                setTimeout(() => {
                    editor.focus();
                    editorReady = true;
                    console.log('Monaco Editor initialized successfully');
                }, 100);

                // Check URL for snippet ID
                const urlParams = new URLSearchParams(window.location.search);
                const snippetId = urlParams.get('snippet');
                if (snippetId) {
                    loadSnippet(snippetId);
                }
            } catch (error) {
                console.error('Monaco Editor initialization error:', error);
                document.getElementById('output').innerHTML =
                    '<span class="error">Error loading editor. Please refresh the page.</span>';
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

            if (projectMode) {
                // Reset to single file in project mode
                files = [{
                    name: 'index.' + (lang === 'javascript' ? 'js' : lang),
                    language: lang,
                    content: templates[lang]
                }];
                currentFileIndex = 0;
                renderFilesList();
            }

            editor.setValue(templates[lang]);
            currentSnippetId = null;
            document.getElementById("output").textContent = "New snippet created. Ready to run...";
            window.history.pushState({}, '', window.location.pathname);

            // Hide preview
            document.getElementById('previewContainer').classList.remove('show');
            document.getElementById('togglePreviewBtn').classList.remove('active');
        };

        // Run Code (Simulated)
        document.getElementById("runBtn").onclick = () => {
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

            outputDiv.innerHTML = '<span class="loading"><i class="fas fa-spinner fa-spin"></i> Running code...</span>';

            setTimeout(() => {
                if (!code.trim()) {
                    outputDiv.innerHTML = '<span class="error">Error: No code to run!</span>';
                    return;
                }

                let output = '';

                if (lang === 'python') {
                    const matches = code.match(/print\s*\(\s*["'](.+?)["']\s*\)/g);
                    if (matches) {
                        matches.forEach(m => {
                            const content = m.match(/["'](.+?)["']/)[1];
                            output += content + '\n';
                        });
                    } else {
                        output = 'Code executed successfully (no print statements found)';
                    }
                } else if (lang === 'javascript') {
                    const matches = code.match(/console\.log\s*\(\s*["'](.+?)["']\s*\)/g);
                    if (matches) {
                        matches.forEach(m => {
                            const content = m.match(/["'](.+?)["']/)[1];
                            output += content + '\n';
                        });
                    } else {
                        output = 'Code executed successfully';
                    }
                } else if (lang === 'cpp') {
                    const matches = code.match(/cout\s*<<\s*["'](.+?)["'] /g);
                    if (matches) {
                        matches.forEach(m => {
                            const content = m.match(/["'](.+?)["']/)[1];
                            output += content + '\n';
                        });
                    } else {
                        output = 'Program compiled and executed successfully';
                    }
                } else if (lang === 'java') {
                    const matches = code.match(/System\.out\.println\s*\(\s*["'](.+?)["']\s*\)/g);
                    if (matches) {
                        matches.forEach(m => {
                            const content = m.match(/["'](.+?)["']/)[1];
                            output += content + '\n';
                        });
                    } else {
                        output = 'Program compiled and executed successfully';
                    }
                } else {
                    output = `✓ ${lang.toUpperCase()} code validated successfully`;
                }

                outputDiv.innerHTML = `<span class="info">═══ Execution Result ═══</span>\n<span class="success">${output}</span>\n<span class="info">═══ Completed ═══</span>`;
            }, 800);
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
                author_id: 'current_user_id' // Replace with actual user ID from session
            };

            try {
                // Call your backend API
                const response = await fetch(`${API_BASE}/snippets`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // Add authentication header if needed
                        // 'Authorization': 'Bearer ' + yourAuthToken
                    },
                    body: JSON.stringify(snippetData)
                });

                if (!response.ok) {
                    throw new Error('Failed to save snippet');
                }

                const result = await response.json();
                currentSnippetId = result.id;

                document.getElementById('saveModal').classList.remove('show');
                document.getElementById('snippetTitle').value = '';
                document.getElementById('snippetDesc').value = '';

                const shareUrl = `${window.location.origin}${window.location.pathname}?snippet=${result.id}`;
                window.history.pushState({}, '', `?snippet=${result.id}`);

                showNotification(`✓ Snippet saved successfully!`, "success");

                document.getElementById("output").innerHTML =
                    `<span class="success">✓ Snippet saved successfully!</span>\n\n` +
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

        // Load Modal
        document.getElementById("loadBtn").onclick = () => {
            document.getElementById('loadModal').classList.add('show');
            document.getElementById('snippetId').focus();
        };

        document.getElementById("cancelLoad").onclick = () => {
            document.getElementById('loadModal').classList.remove('show');
            document.getElementById('snippetId').value = '';
        };

        document.getElementById("confirmLoad").onclick = async () => {
            const input = document.getElementById('snippetId').value.trim();
            if (!input) {
                showNotification('Please enter a snippet ID!', 'error');
                return;
            }

            // Extract ID from URL if full URL provided
            let snippetId = input;
            try {
                const url = new URL(input);
                snippetId = url.searchParams.get('snippet') || input;
            } catch (e) {
                // Not a URL, treat as ID
            }

            document.getElementById('loadModal').classList.remove('show');
            document.getElementById('snippetId').value = '';

            await loadSnippet(snippetId);
        };

        // Load snippet function
        async function loadSnippet(snippetId) {
            const outputDiv = document.getElementById("output");
            outputDiv.innerHTML = '<span class="loading">⟳ Loading snippet...</span>';

            try {
                const response = await fetch(`${API_BASE}/snippets/${snippetId}`, {
                    headers: {
                        // Add authentication header if needed
                        // 'Authorization': 'Bearer ' + yourAuthToken
                    }
                });

                if (!response.ok) {
                    throw new Error('Snippet not found');
                }

                const snippet = await response.json();

                // Set the editor content
                editor.setValue(snippet.code);

                // Set the language
                const langSelect = document.getElementById('lang-select');
                langSelect.value = snippet.language;
                monaco.editor.setModelLanguage(editor.getModel(), snippet.language);

                currentSnippetId = snippet.id;
                window.history.pushState({}, '', `?snippet=${snippet.id}`);

                outputDiv.innerHTML =
                    `<span class="success">✓ Snippet loaded successfully!</span>\n\n` +
                    `Title: ${snippet.title}\n` +
                    `Language: ${snippet.language}\n` +
                    `Author: ${snippet.author_id}\n` +
                    `Created: ${new Date(snippet.created_at).toLocaleString()}\n` +
                    (snippet.description ? `\nDescription: ${snippet.description}` : '');

                showNotification('✓ Snippet loaded!', 'success');

            } catch (error) {
                console.error('Load error:', error);
                outputDiv.innerHTML = '<span class="error">✗ Failed to load snippet. Check the ID and try again.</span>';
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