const express = require('express');
const router = express.Router();

module.exports = (pool) => {
  // Get all bookings
  router.get('/', async (req, res) => {
    try {
      const [rows] = await pool.query(`
        SELECT b.*, r.room_number, r.room_type, u.username, u.email 
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        JOIN users u ON b.user_id = u.user_id
        ORDER BY b.created_at DESC
      `);
      res.json(rows);
    } catch (error) {
      res.status(500).json({ error: 'Failed to fetch bookings', details: error.message });
    }
  });

  // Get bookings by user ID
  router.get('/user/:userId', async (req, res) => {
    try {
      const userId = req.params.userId;
      const [rows] = await pool.query(`
        SELECT b.*, r.room_number, r.room_type, r.image_url 
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
      `, [userId]);
      res.json(rows);
    } catch (error) {
      res.status(500).json({ error: 'Failed to fetch user bookings', details: error.message });
    }
  });

  // Get a specific booking by ID
  router.get('/:id', async (req, res) => {
    try {
      const [rows] = await pool.query(`
        SELECT b.*, r.room_number, r.room_type, r.price_per_night, r.image_url, u.username, u.email
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.booking_id = ?
      `, [req.params.id]);
      
      if (rows.length === 0) {
        return res.status(404).json({ error: 'Booking not found' });
      }
      
      res.json(rows[0]);
    } catch (error) {
      res.status(500).json({ error: 'Failed to fetch booking', details: error.message });
    }
  });

  // Create a new booking
  router.post('/', async (req, res) => {
    try {
      const { user_id, room_id, check_in_date, check_out_date, adults, children, total_price } = req.body;
      
      // Check if room exists and is available
      const [roomCheck] = await pool.query('SELECT * FROM rooms WHERE room_id = ? AND status = "available"', [room_id]);
      if (roomCheck.length === 0) {
        return res.status(400).json({ error: 'Room not found or not available' });
      }
      
      // Check if room is already booked for the requested dates
      const [bookingCheck] = await pool.query(`
        SELECT * FROM bookings 
        WHERE room_id = ? 
        AND booking_status IN ('confirmed', 'checked_in') 
        AND (
          (check_in_date <= ? AND check_out_date >= ?) OR
          (check_in_date <= ? AND check_out_date >= ?) OR
          (check_in_date >= ? AND check_out_date <= ?)
        )
      `, [room_id, check_in_date, check_in_date, check_out_date, check_out_date, check_in_date, check_out_date]);
      
      if (bookingCheck.length > 0) {
        return res.status(400).json({ error: 'Room is not available for the selected dates' });
      }

      const [result] = await pool.query(
        'INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date, adults, children, total_price, booking_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [user_id, room_id, check_in_date, check_out_date, adults || 1, children || 0, total_price, 'pending']
      );
      
      res.status(201).json({ 
        message: 'Booking created successfully',
        booking_id: result.insertId 
      });
    } catch (error) {
      res.status(500).json({ error: 'Failed to create booking', details: error.message });
    }
  });

  // Update booking status
  router.patch('/:id/status', async (req, res) => {
    try {
      const bookingId = req.params.id;
      const { status } = req.body;
      
      const validStatuses = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];
      if (!validStatuses.includes(status)) {
        return res.status(400).json({ error: 'Invalid status value' });
      }
      
      const [result] = await pool.query('UPDATE bookings SET booking_status = ? WHERE booking_id = ?', [status, bookingId]);
      
      if (result.affectedRows === 0) {
        return res.status(404).json({ error: 'Booking not found' });
      }
      
      res.json({ message: 'Booking status updated successfully' });
    } catch (error) {
      res.status(500).json({ error: 'Failed to update booking status', details: error.message });
    }
  });

  // Cancel a booking
  router.patch('/:id/cancel', async (req, res) => {
    try {
      const bookingId = req.params.id;
      
      // Check if booking exists and can be cancelled
      const [bookingCheck] = await pool.query('SELECT * FROM bookings WHERE booking_id = ?', [bookingId]);
      
      if (bookingCheck.length === 0) {
        return res.status(404).json({ error: 'Booking not found' });
      }
      
      if (bookingCheck[0].booking_status === 'checked_in' || bookingCheck[0].booking_status === 'checked_out') {
        return res.status(400).json({ error: 'Cannot cancel a booking that is already checked in or checked out' });
      }
      
      const [result] = await pool.query('UPDATE bookings SET booking_status = "cancelled" WHERE booking_id = ?', [bookingId]);
      
      res.json({ message: 'Booking cancelled successfully' });
    } catch (error) {
      res.status(500).json({ error: 'Failed to cancel booking', details: error.message });
    }
  });

  return router;
};