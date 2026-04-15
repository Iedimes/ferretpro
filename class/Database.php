<?php
class Database extends PDO {
    private static $instance = null;
    
    private function __construct() {
        $dbPath = dirname(__DIR__) . '/storage/ferrepro.db';
        parent::__construct("sqlite:$dbPath");
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->initTables();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initTables() {
        $this->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE,
                password TEXT NOT NULL,
                role TEXT DEFAULT 'vendedor',
                active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                parent_id INTEGER,
                active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE IF NOT EXISTS providers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                ruc TEXT,
                contact_name TEXT,
                phone TEXT,
                email TEXT,
                address TEXT,
                notes TEXT,
                active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE IF NOT EXISTS clients (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                document_type TEXT DEFAULT 'DNI',
                document TEXT,
                phone TEXT,
                email TEXT,
                address TEXT,
                credit_limit REAL DEFAULT 0,
                category TEXT DEFAULT 'minorista',
                balance REAL DEFAULT 0,
                active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT UNIQUE,
                barcode TEXT,
                name TEXT NOT NULL,
                description TEXT,
                category_id INTEGER,
                provider_id INTEGER,
                unit TEXT DEFAULT 'unidad',
                cost_price REAL DEFAULT 0,
                sale_price REAL DEFAULT 0,
                wholesale_price REAL DEFAULT 0,
                iva INTEGER DEFAULT 10,
                stock INTEGER DEFAULT 0,
                min_stock INTEGER DEFAULT 5,
                location TEXT,
                active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id),
                FOREIGN KEY (provider_id) REFERENCES providers(id)
            );
            
            CREATE TABLE IF NOT EXISTS sales (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id INTEGER,
                user_id INTEGER,
                type TEXT DEFAULT 'contado',
                status TEXT DEFAULT 'pagada',
                subtotal REAL DEFAULT 0,
                discount REAL DEFAULT 0,
                total REAL DEFAULT 0,
                payment_method TEXT DEFAULT 'efectivo',
                delivery_type TEXT DEFAULT 'mostrador',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (client_id) REFERENCES clients(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            );
            
            CREATE TABLE IF NOT EXISTS sale_details (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sale_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                unit_price REAL NOT NULL,
                discount REAL DEFAULT 0,
                subtotal REAL NOT NULL,
                FOREIGN KEY (sale_id) REFERENCES sales(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            );
            
            CREATE TABLE IF NOT EXISTS accounts_receivable (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id INTEGER NOT NULL,
                sale_id INTEGER,
                amount REAL NOT NULL,
                paid_amount REAL DEFAULT 0,
                due_date DATE,
                status TEXT DEFAULT 'pendiente',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (client_id) REFERENCES clients(id),
                FOREIGN KEY (sale_id) REFERENCES sales(id)
            );
            
            CREATE TABLE IF NOT EXISTS payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                account_receivable_id INTEGER,
                user_id INTEGER,
                amount REAL NOT NULL,
                payment_method TEXT DEFAULT 'efectivo',
                reference TEXT,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (account_receivable_id) REFERENCES accounts_receivable(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            );
            
            CREATE TABLE IF NOT EXISTS stock_movements (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                type TEXT NOT NULL,
                quantity INTEGER NOT NULL,
                reference_type TEXT,
                reference_id INTEGER,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id)
            );
            
            CREATE TABLE IF NOT EXISTS settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key TEXT UNIQUE NOT NULL,
                value TEXT
            );
            
            CREATE TABLE IF NOT EXISTS password_resets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                token TEXT UNIQUE NOT NULL,
                email TEXT NOT NULL,
                used INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id)
            );
            
            CREATE TABLE IF NOT EXISTS login_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                ip_address TEXT,
                user_agent TEXT,
                status TEXT DEFAULT 'success',
                failed_reason TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            );
            
            CREATE TABLE IF NOT EXISTS session_recovery (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                recovery_token TEXT UNIQUE NOT NULL,
                session_token TEXT,
                ip_address TEXT,
                used INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id)
            );
            
            CREATE TABLE IF NOT EXISTS credit_notes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sale_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                client_id INTEGER,
                reason TEXT NOT NULL,
                subtotal REAL NOT NULL,
                discount REAL DEFAULT 0,
                total REAL NOT NULL,
                status TEXT DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (sale_id) REFERENCES sales(id),
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (client_id) REFERENCES clients(id)
            );
            
            CREATE TABLE IF NOT EXISTS credit_note_details (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                credit_note_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                unit_price REAL NOT NULL,
                subtotal REAL NOT NULL,
                FOREIGN KEY (credit_note_id) REFERENCES credit_notes(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            );
            
            CREATE TABLE IF NOT EXISTS quotes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id INTEGER,
                client_name TEXT,
                client_document TEXT,
                user_id INTEGER NOT NULL,
                subtotal REAL NOT NULL,
                discount REAL DEFAULT 0,
                total REAL NOT NULL,
                validity_days INTEGER DEFAULT 30,
                notes TEXT,
                status TEXT DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (client_id) REFERENCES clients(id)
            );
            
            CREATE TABLE IF NOT EXISTS quote_details (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                quote_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                unit_price REAL NOT NULL,
                subtotal REAL NOT NULL,
                FOREIGN KEY (quote_id) REFERENCES quotes(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            );
            
            CREATE TABLE IF NOT EXISTS purchases (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                provider_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                invoice_number TEXT,
                subtotal REAL NOT NULL,
                discount REAL DEFAULT 0,
                total REAL NOT NULL,
                payment_method TEXT DEFAULT 'contado',
                status TEXT DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (provider_id) REFERENCES providers(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            );
            
            CREATE TABLE IF NOT EXISTS purchase_details (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                purchase_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                unit_cost REAL NOT NULL,
                subtotal REAL NOT NULL,
                FOREIGN KEY (purchase_id) REFERENCES purchases(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            );
            
            CREATE TABLE IF NOT EXISTS cash_register (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                opening_amount REAL NOT NULL,
                closing_amount REAL,
                expected_amount REAL,
                difference REAL,
                status TEXT DEFAULT 'open',
                opening_notes TEXT,
                closing_notes TEXT,
                opened_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                closed_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id)
            );
            
            CREATE TABLE IF NOT EXISTS cash_movements (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                cash_register_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                type TEXT NOT NULL,
                amount REAL NOT NULL,
                description TEXT,
                payment_method TEXT DEFAULT 'efectivo',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (cash_register_id) REFERENCES cash_register(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            );
            
            CREATE TABLE IF NOT EXISTS accounts_payable (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                provider_id INTEGER NOT NULL,
                purchase_id INTEGER,
                amount REAL NOT NULL,
                paid_amount REAL DEFAULT 0,
                due_date DATE,
                status TEXT DEFAULT 'pendiente',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (provider_id) REFERENCES providers(id),
                FOREIGN KEY (purchase_id) REFERENCES purchases(id)
            );
            
            CREATE TABLE IF NOT EXISTS expenses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                category TEXT NOT NULL,
                description TEXT,
                amount REAL NOT NULL,
                payment_method TEXT DEFAULT 'efectivo',
                reference TEXT,
                date DATE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            );
        ");
        
        try {
            $this->exec("ALTER TABLE sales ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        } catch (Exception $e) {}
        
        try {
            $this->exec("ALTER TABLE products ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        } catch (Exception $e) {}
        
        try {
            $this->exec("ALTER TABLE clients ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        } catch (Exception $e) {}
        
        try {
            $this->exec("ALTER TABLE providers ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        } catch (Exception $e) {}
        
        try {
            $this->exec("ALTER TABLE providers ADD COLUMN ruc TEXT");
        } catch (Exception $e) {}
        
        try {
            $this->exec("ALTER TABLE categories ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        } catch (Exception $e) {}
        
        try {
            $this->exec("ALTER TABLE accounts_receivable ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        } catch (Exception $e) {}
        
        try {
            $this->exec("ALTER TABLE users ADD COLUMN last_login DATETIME");
        } catch (Exception $e) {}
        
        try {
            $this->exec("ALTER TABLE users ADD COLUMN last_password_change DATETIME");
        } catch (Exception $e) {}
        
        try {
            $this->exec("ALTER TABLE users ADD COLUMN failed_login_attempts INTEGER DEFAULT 0");
        } catch (Exception $e) {}
        
        try {
            $this->exec("ALTER TABLE users ADD COLUMN locked_until DATETIME");
        } catch (Exception $e) {}
        
        try {
            $this->exec("ALTER TABLE users ADD COLUMN two_fa_enabled INTEGER DEFAULT 0");
        } catch (Exception $e) {}
        
        try {
            $this->exec("ALTER TABLE users ADD COLUMN two_fa_secret TEXT");
        } catch (Exception $e) {}
        
        $this->seedDefaultData();
    }
    
    private function seedDefaultData() {
        $stmt = $this->query("SELECT COUNT(*) as count FROM users");
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] == 0) {
            $this->exec("INSERT INTO users (name, email, password, role) VALUES 
                ('Administrador', 'admin@ferrepro.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin')");
            
            $this->exec("INSERT INTO categories (name, description) VALUES 
                ('Herramientas', 'Herramientas manuales y eléctricas'),
                ('Pinturas', 'Pinturas y afines'),
                ('Tornillos', 'Tornillos y tuercas'),
                ('Eléctrico', 'Material eléctrico'),
                ('Fontanería', 'Tubos y conexiones'),
                ('Madera', 'Tablas y madera'),
                ('Protección', 'Elementos de protección'),
                ('Jardinería', 'Herramientas de jardín')");
            
            $this->exec("INSERT INTO providers (name, contact_name, phone) VALUES 
                ('Proveedor Principal', 'Juan Perez', '11-1234-5678'),
                ('Ferretería ABC', 'Maria García', '11-8765-4321')");
            
            $this->exec("INSERT INTO clients (name, document, phone, category) VALUES 
                ('Cliente Mostrador', '00000000', '', 'minorista'),
                ('Juan Gómez', '20123456789', '11-1111-1111', 'minorista'),
                ('María López', '27123456789', '11-2222-2222', 'mayorista'),
                ('Ferretería El Tornillo', '30123456789', '11-3333-3333', 'revendedor')");
            
            $this->exec("INSERT INTO products (code, name, description, category_id, provider_id, unit, cost_price, sale_price, stock, min_stock) VALUES 
                ('HERR-001', 'Martillo', 'Martillo de bola de acero', 1, 1, 'unidad', 150, 250, 50, 10),
                ('HERR-002', 'Destornillador', 'Juego de destornilladores 6 piezas', 1, 1, 'set', 300, 450, 30, 5),
                ('HERR-003', 'Alicates', 'Alicates profesional', 1, 1, 'unidad', 200, 320, 25, 5),
                ('HERR-004', 'Sierra manual', 'Sierra para metal', 1, 2, 'unidad', 180, 280, 15, 3),
                ('PINT-001', 'Latex Blanco', 'Pintura látex blanca 20L', 2, 2, 'litro', 800, 1200, 20, 5),
                ('PINT-002', 'Esmalte sintetico', 'Esmalte sintético rojo', 2, 2, 'litro', 350, 550, 15, 3),
                ('TORN-001', 'Tornillos madera', 'Tornillos para madera x100', 3, 1, 'pak', 50, 80, 100, 20),
                ('TORN-002', 'Tuercas', 'Tuercas assorted x50', 3, 1, 'pak', 40, 65, 80, 20),
                ('ELEC-001', 'Cable 2.5mm', 'Cable eléctrico 2.5mm metro', 4, 1, 'metro', 80, 120, 500, 100),
                ('ELEC-002', 'Interruptor', 'Interruptor simple', 4, 1, 'unidad', 50, 85, 40, 10),
                ('FONT-001', 'Canilla', 'Canilla de cocina', 5, 2, 'unidad', 250, 400, 20, 5),
                ('FONT-002', 'Flexible', 'Manguera flexible 40cm', 5, 2, 'unidad', 80, 130, 30, 5),
                ('JARD-001', 'Manguera', 'Manguera de jardín 30m', 8, 2, 'unidad', 800, 1200, 10, 2),
                ('PROT-001', 'Guantes', 'Guantes de trabajo', 7, 1, 'par', 100, 180, 50, 10),
                ('PROT-002', 'Antiparras', 'Antiparras de protección', 7, 1, 'unidad', 80, 140, 30, 5)");
            
            $this->exec("INSERT INTO settings (key, value) VALUES 
                ('company_name', 'Ferretería FerrePro'),
                ('company_address', ''),
                ('company_phone', ''),
                ('company_email', ''),
                ('company_document', ''),
                ('invoice_prefix', 'A'),
                ('invoice_next', '1'),
                ('iva_percentage', '21'),
                ('default_discount', '0'),
                ('low_stock_alert', '5')");
        }
    }
}
