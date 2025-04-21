const express = require('express');
const router = express.Router();

module.exports = (pool) => {
  // Get all payments
  router.get('/', async (req, res) => {
    try {
      const [rows] = await pool.query(`
        SELECT p.*, b.user_id, b.check_in_date, b.check_out_date, r.room_number, u.username, u.email
        FROM payments p
        JOIN bookings b ON p.booking_id = b.booking_id
        JOIN rooms r ON b.room_id = r.room_id
        JOIN users u ON b.user_id = u.user_id
        ORDER BY p.payment_date DESC
      `);
      res.json(rows);
    } catch (error) {
      res.status(500).json({ error: 'Failed to fetch payments', details: error.message });
    }
  });

  // Get payments by user ID
  router.get('/user/:userId', async (req, res) => {
    try {
      const userId = req.params.userId;
      const [rows] = await pool.query(`
        SELECT p.*, b.check_in_date, b.check_out_date, r.room_number, r.room_type
        FROM payments p
        JOIN bookings b ON p.booking_id = b.booking_id
        JOIN rooms r ON b.room_id = r.room_id
        WHERE b.user_id = ?
        ORDER BY p.payment_date DESC
      `, [userId]);
      res.json(rows);
    } catch (error) {
      res.status(500).json({ error: 'Failed to fetch user payments', details: error.message });
    }
  });

  // Get a specific payment by ID
  router.get('/:id', async (req, res) => {
    try {
      const [rows] = await pool.query(`
        SELECT p.*, b.user_id, b.check_in_date, b.check_out_date, r.room_number, r.room_type, u.username, u.email
        FROM payments p
        JOIN bookings b ON p.booking_id = b.booking_id
        JOIN rooms r ON b.room_id = r.room_id
        JOIN users u ON b.user_id = u.user_id
        WHERE p.payment_id = ?
      `, [req.params.id]);
      
      if (rows.length === 0) {
        return res.status(404).json({ error: 'Payment not found' });
      }
      
      res.json(rows[0]);
    } catch (error) {
      res.status(500).json({ error: 'Failed to fetch payment', details: error.message });
    }
  });

  // Create a new payment
  router.post('/', async (req, res) => {
    try {
      const { booking_id, amount, payment_method, transaction_id } = req.body;
      
      // Check if booking exists
      const [bookingCheck] = await pool.query('SELECT * FROM bookings WHERE booking_id = ?', [booking_id]);
      if (bookingCheck.length === 0) {
        return res.status(400).json({ error: 'Booking not found' });
      }
      
      // Check if payment already exists for this booking
      const [paymentCheck] = await pool.query('SELECT * FROM payments WHERE booking_id = ? AND status = "completed"', [booking_id]);
      if (paymentCheck.length > 0) {
        return res.status(400).json({ error: 'Payment already exists for this booking' });
      }

      const [result] = await pool.query(
        'INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?)',
        [booking_id, amount, payment_method, transaction_id, 'completed']
      );
      
      // Update booking status to confirmed
      await pool.query('UPDATE bookings SET booking_status = "confirmed" WHERE booking_id = ?', [booking_id]);
      
      res.status(201).json({ 
        message: 'Payment processed successfully',
        payment_id: result.insertId 
      });
    } catch (error) {
      res.status(500).json({ error: 'Failed to process payment', details: error.message });
    }
  });

  // Update payment status
//   router.patch('/:id/status', async (req, res) => {
//     try {
//       const paymentId = req.params.id;
//       const { status } = req.body;
      
//       const validStatuses = ['pending', 'completed', 'failed', 'refunded'];
//       if (!validStatuses.includes(status)) {
//         return res.status(400).json({ error: 'Invalid status value' });
//       }
      
//       const [paymentCheck] = await pool.query('SELECT * FROM payments WHERE payment_id = ?', [paymentId]);
//       if (paymentCheck.length === 0) {
//         return res.status(404).json({ error: 'Payment not found' });
//       }
      
//       await pool.query('UPDATE payments SET status = ? WHERE payment_id = ?', [status, paymentId]);
      
//       // If payment is refunded, update booking status
//       if (status === 'refunded') {
//         await pool.query('UPDATE bookings SET booking_status = "cancelled" WHERE booking_id = ?', [paymentCheck[0].booking_id]);
//       }
      
//       res.json({ message: 'Payment status updated successfully' });
//     } catch (error) {
//       res.status(500).json({ error: 'Failed to update payment status', details: error.

}