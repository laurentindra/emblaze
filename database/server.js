require('dotenv').config();
const express = require('express');
const mysql = require('mysql2');
const bcrypt = require('bcrypt');
const path = require('path'); 
const cors = require('cors');

const app = express();

// --- SETUP FRONTEND EMBLAZE ---
app.use(express.static(path.join(__dirname, '..', 'frontend', 'public')));
app.use(express.static(path.join(__dirname, '..', 'frontend', 'views')));

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const port = process.env.PORT || 3000;

const db = mysql.createConnection({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME
});

db.connect((err) => {
    if (err) {
        console.error('Database connection failed: ' + err.message);
        return;
    }
    console.log('Database ' + process.env.DB_NAME + ' Successfully connected! ✅');
    
    // --- 3NF TABLE STRUCTURE ---
    
    const userTable = `
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL, 
        email VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )`;

    const productTable = `
    CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        stock INT DEFAULT 0,
        category VARCHAR(100),
        image_url VARCHAR(255)
    )`;

    // TABLE ORDERS (Induk - 3NF)
    const orderTable = `
    CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(50) DEFAULT 'Pending',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )`;

    // TABLE ORDER_ITEMS (Anak - 3NF Detail)
    const orderItemsTable = `
    CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        quantity INT DEFAULT 1,
        price_at_purchase DECIMAL(10, 2),
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )`;

    db.query(userTable, () => {
        db.query(productTable, () => {
            db.query(orderTable, () => {
                db.query(orderItemsTable, (err) => {
                    if (err) console.log("Failed to create tables", err);
                    else console.log("All 3NF Tables are ready! 🚀");
                });
            });
        });
    });
});

// --- ROUTES ---
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, '..', 'frontend', 'views', 'index.html'));
});

// Register & Login 
app.post('/api/register', async (req, res) => {
    const { username, password, email } = req.body;
    try {
        const hashedPassword = await bcrypt.hash(password, 10);
        const query = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
        db.query(query, [username, hashedPassword, email], (err) => {
            if (err) return res.status(500).json({ message: "Cannot sign in/User exists!" });
            res.status(201).json({ message: "User registered as safe!" });
        });
    } catch (e) { res.status(500).send("Error hashing"); }
});

app.post('/api/login', (req, res) => {
    const { username, password } = req.body;
    const query = "SELECT * FROM users WHERE username = ?";
    db.query(query, [username], async (err, results) => {
        if (err || results.length === 0) return res.status(404).json({ message: "User not found!" });
        const match = await bcrypt.compare(password, results[0].password);
        if (match) res.status(200).json({ message: `Login successful! hi ${username}!`, user_id: results[0].id });
        else res.status(401).json({ message: "your password is wrong." });
    });
});

// --- PRODUCT ROUTES ---
app.get('/api/products', (req, res) => {
    db.query("SELECT * FROM products", (err, results) => {
        res.status(200).json(results);
    });
});

// --- ORDER ROUTE
app.post('/api/orders', (req, res) => {
    const { user_id, cart_items } = req.body; // cart_items harus berupa array barang

    // 1. Masukin ke tabel induk (orders)
    const orderQuery = "INSERT INTO orders (user_id) VALUES (?)";
    db.query(orderQuery, [user_id], (err, result) => {
        if (err) return res.status(500).json({ message: "Order failed at parent table." });
        
        const orderId = result.insertId;

        // 2. Masukin semua barang dari cart ke tabel detail (order_items)
        // Kita asumsikan cart_items isinya [{product_id: 1, quantity: 2, price: 50000}, ...]
        const values = cart_items.map(item => [orderId, item.product_id, item.quantity, item.price]);
        const itemsQuery = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES ?";
        
        db.query(itemsQuery, [values], (err) => {
            if (err) return res.status(500).json({ message: "Failed to save order items." });
            res.status(201).json({ message: "Order placed successfully in 3NF!", orderId });
        });
    });
});

app.listen(port, () => {
    console.log(`🚀 Server EMBLAZE jalan di http://localhost:${port}`);
});