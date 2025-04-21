const express = require('express');
const router = express.Router();
const { authenticateJWT } = require('../middleware/auth');

/**
 * @route   GET /api/rooms
 * @desc    Get all rooms or filter by availability
 * @access  Public
 */
router.get('/', async (req, res) => {
  try {
    const { check_in, check_out, room_type, capacity, status } = req.query;
    let query = 'SELECT * FROM rooms';
    const queryParams = [];
    const conditions = [];

    // Apply filters
    if (status) {
      conditions.push('status = ?');
      queryParams.push(status);
    }

    if (room_type) {
      conditions.push('room_type = ?');
      queryParams.push(room_type);
    }

    if (capacity) {
      conditions.push('capacity >= ?');
      queryParams.push(parseInt(capacity));
    }

    if (conditions.length > 0) {
      query += ' WHERE ' + conditions.join(' AND ');
    }

    // Check availability for the given dates
    if (check_in && check_out) {
      const availabilitySubquery = `
        SELECT room_id FROM bookings 
        WHERE booking_status IN ('confirmed', 'checked_in') 
        AND (
          (check_in_date <= ? AND check_out_date >= ?) OR
          (check_in_date <= ? AND check_out_date >= ?) OR
          (check_in_date >= ? AND check_out_date <= ?)
        )
      `;
      
      if (conditions.length > 0) {
        query += ' AND ';
      } else {
        query += ' WHERE ';
      }
      
      query += `room_id NOT IN (${availabilitySubquery})`;
      queryParams.push(check_in, check_in, check_out, check_out, check_in, check_out);
    }

    const [rooms] = await req.db.execute(query, queryParams);
    
    res.json({
      status: 'success',
      data: rooms
    });
  } catch (error) {
    console.error('Error fetching rooms:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to fetch rooms'
    });
  }
});

/**
 * @route   GET /api/rooms/:id
 * @desc    Get a single room by ID
 * @access  Public
 */
router.get('/:id', async (req, res) => {
  try {
    const [rooms] = await req.db.execute(
      'SELECT * FROM rooms WHERE room_id = ?',
      [req.params.id]
    );

    if (rooms.length === 0) {
      return res.status(404).json({
        status: 'error',
        message: 'Room not found'
      });
    }

    res.json({
      status: 'success',
      data: rooms[0]
    });
  } catch (error) {
    console.error('Error fetching room:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to fetch room'
    });
  }
});

/**
 * @route   POST /api/rooms
 * @desc    Create a new room
 * @access  Admin
 */
router.post('/', authenticateJWT, async (req, res) => {
  // Check if user is admin
  if (req.user.role !== 'admin') {
    return res.status(403).json({
      status: 'error',
      message: 'Unauthorized'
    });
  }

  try {
    const { room_number, room_type, capacity, price_per_night, description, amenities, image_url } = req.body;

    // Validate required fields
    if (!room_number || !room_type || !capacity || !price_per_night) {
      return res.status(400).json({
        status: 'error',
        message: 'Missing required fields'
      });
    }

    // Check if room number already exists
    const [existingRooms] = await req.db.execute(
      'SELECT * FROM rooms WHERE room_number = ?',
      [room_number]
    );

    if (existingRooms.length > 0) {
      return res.status(400).json({
        status: 'error',
        message: 'Room number already exists'
      });
    }

    // Insert new room
    const [result] = await req.db.execute(
      'INSERT INTO rooms (room_number, room_type, capacity, price_per_night, description, amenities, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
      [room_number, room_type, capacity, price_per_night, description || null, amenities || null, image_url || null, 'available']
    );

    // Get the newly created room
    const [newRoom] = await req.db.execute(
      'SELECT * FROM rooms WHERE room_id = ?',
      [result.insertId]
    );

    res.status(201).json({
      status: 'success',
      message: 'Room created successfully',
      data: newRoom[0]
    });
  } catch (error) {
    console.error('Error creating room:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to create room'
    });
  }
});

/**
 * @route   PUT /api/rooms/:id
 * @desc    Update a room
 * @access  Admin
 */
router.put('/:id', authenticateJWT, async (req, res) => {
  // Check if user is admin
  if (req.user.role !== 'admin') {
    return res.status(403).json({
      status: 'error',
      message: 'Unauthorized'
    });
  }

  try {
    const { room_number, room_type, capacity, price_per_night, description, amenities, image_url, status } = req.body;
    const roomId = req.params.id;

    // Check if room exists
    const [existingRooms] = await req.db.execute(
      'SELECT * FROM rooms WHERE room_id = ?',
      [roomId]
    );

    if (existingRooms.length === 0) {
      return res.status(404).json({
        status: 'error',
        message: 'Room not found'
      });
    }

    // Check if room number already exists (for a different room)
    if (room_number) {
      const [duplicateRooms] = await req.db.execute(
        'SELECT * FROM rooms WHERE room_number = ? AND room_id != ?',
        [room_number, roomId]
      );

      if (duplicateRooms.length > 0) {
        return res.status(400).json({
          status: 'error',
          message: 'Room number already exists'
        });
      }
    }

    // Build update query
    const updates = [];
    const queryParams = [];

    if (room_number) {
      updates.push('room_number = ?');
      queryParams.push(room_number);
    }

    if (room_type) {
      updates.push('room_type = ?');
      queryParams.push(room_type);
    }

    if (capacity) {
      updates.push('capacity = ?');
      queryParams.push(capacity);
    }

    if (price_per_night) {
      updates.push('price_per_night = ?');
      queryParams.push(price_per_night);
    }

    if (description !== undefined) {
      updates.push('description = ?');
      queryParams.push(description);
    }

    if (amenities !== undefined) {
      updates.push('amenities = ?');
      queryParams.push(amenities);
    }

    if (image_url !== undefined) {
      updates.push('image_url = ?');
      queryParams.push(image_url);
    }

    if (status) {
      updates.push('status = ?');
      queryParams.push(status);
    }

    // If no updates, return the existing room
    if (updates.length === 0) {
      return res.json({
        status: 'success',
        message: 'No changes made',
        data: existingRooms[0]
      });
    }

    // Add room_id to query params
    queryParams.push(roomId);

    // Update room
    await req.db.execute(
      `UPDATE rooms SET ${updates.join(', ')} WHERE room_id = ?`,
      queryParams
    );

    // Get the updated room
    const [updatedRoom] = await req.db.execute(
      'SELECT * FROM rooms WHERE room_id = ?',
      [roomId]
    );

    res.json({
      status: 'success',
      message: 'Room updated successfully',
      data: updatedRoom[0]
    });
  } catch (error) {
    console.error('Error updating room:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to update room'
    });
  }
});

/**
 * @route   DELETE /api/rooms/:id
 * @desc    Delete a room
 * @access  Admin
 */
router.delete('/:id', authenticateJWT, async (req, res) => {
  // Check if user is admin
  if (req.user.role !== 'admin') {
    return res.status(403).json({
      status: 'error',
      message: 'Unauthorized'
    });
  }

  try {
    const roomId = req.params.id;

    // Check if room exists
    const [existingRooms] = await req.db.execute(
      'SELECT * FROM rooms WHERE room_id = ?',
      [roomId]
    );

    if (existingRooms.length === 0) {
      return res.status(404).json({
        status: 'error',
        message: 'Room not found'
      });
    }

    // Check if room has bookings
    const [bookings] = await req.db.execute(
      'SELECT * FROM bookings WHERE room_id = ? AND booking_status IN ("confirmed", "checked_in")',
      [roomId]
    );

    if (bookings.length > 0) {
      return res.status(400).json({
        status: 'error',
        message: 'Cannot delete room with active bookings'
      });
    }

    // Delete room
    await req.db.execute(
      'DELETE FROM rooms WHERE room_id = ?',
      [roomId]
    );

    res.json({
      status: 'success',
      message: 'Room deleted successfully'
    });
  } catch (error) {
    console.error('Error deleting room:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to delete room'
    });
  }
});

module.exports = router;