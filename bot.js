// bot.js
const express = require('express');
const bodyParser = require('body-parser');
const TelegramBot = require('node-telegram-bot-api');
const axios = require('axios');
const cron = require('node-cron');
const mongoose = require('mongoose');

const app = express();
const PORT = 3000;
app.use(bodyParser.json());
// MongoDB setup
mongoose.connect('mongodb://localhost/Telegram_Bot', {
    useNewUrlParser: true,
    useUnifiedTopology: true,
});

const db = mongoose.connection;
db.on('error', console.error.bind(console, 'MongoDB connection error:'));
db.once('open', () => {
    console.log('Connected to MongoDB');
});

// MongoDB User model
const userSchema = new mongoose.Schema({
    chatId: { type: Number, required: true, unique: true },
    name: { type: String, required: true },
    city: { type: String, required: true },
    country: { type: String, required: true },
    blocked: { type: Boolean, default: false },
});

const User = mongoose.model('User', userSchema);

// MongoDB admin model
const adminSettingsSchema = new mongoose.Schema({
    apiKey: { type: String, required: true },
    adminToken: { type: String, required: true },
    messageFrequency: { type: String, required: true },
});

const AdminSettings = mongoose.model('AdminSettings', adminSettingsSchema);

// Telegram bot setup
const botToken = '7092516248:AAFJPfNXqF0cG0WC3nhQO91AemV85_srPjU';
let weatherApiKey = 'a73b990ef76521f913ebf095e22ba464'; // Get your API key from OpenWeatherMap

let adminToken = 'JaiShreeRam';
let adminSettings = {}; // To store the admin settings

const bot = new TelegramBot(botToken, { polling: true });

// Function to get weather updates from OpenWeatherMap
async function getWeatherUpdate(city, country) {
    const apiUrl = `http://api.openweathermap.org/data/2.5/weather?q=${city},${country}&appid=${weatherApiKey}`;
    try {
        const response = await axios.get(apiUrl);
        const weatherData = response.data.weather[0].description;
        return `Weather Update for ${city}, ${country}: ${weatherData}`;
    } catch (error) {
        console.error('Error fetching weather data:', error.message);
        return 'Unable to fetch weather data at the moment.';
    }
}

// Function to send daily weather updates to all users
async function sendDailyWeatherUpdates() {
    const users = await User.find({ blocked: false });

    users.forEach(async (user) => {
        const { chatId, city, country } = user;
        const weatherUpdate = await getWeatherUpdate(city, country);
        bot.sendMessage(chatId, weatherUpdate);
    });
}

// Schedule daily weather updates at a specific time (e.g., 12:00 PM UTC)
cron.schedule('0 12 * * *', sendDailyWeatherUpdates);

// Middleware for admin authentication
app.use((req, res, next) => {
    const adminTokenHeader = req.headers.authorization;
    if (adminTokenHeader === adminToken) {
        next();
    } else {
        res.status(401).json({ error: 'Unauthorized' });
    }
});

// Endpoint to get all users
app.get('/users', async (req, res) => {
    const users = await User.find();
    res.json(users);
});

// Endpoint to block a user by chatId
app.post('/blockUser', async (req, res) => {
    const { chatId } = req.body;
    !chatId && res.status(412).json({ message: 'Precondition Failed' });
    await User.findOneAndUpdate({ chatId }, { blocked: true });
    res.json({ message: 'User blocked successfully' });
});

// Endpoint to unblock a user by chatId
app.post('/unblockUser', async (req, res) => {
    const { chatId } = req.body;
    !chatId && res.status(412).json({ message: 'Precondition Failed' });
    await User.findOneAndUpdate({ chatId }, { blocked: false });
    res.json({ message: 'User unblocked successfully' });
});

// API endpoint to update OpenWeatherMap API key
app.post('/updateApiKey', async (req, res) => {
    try {
        const { apiKey } = req.body;
        weatherApiKey = apiKey;
        await AdminSettings.findOneAndUpdate({}, { apiKey });
        res.json({ message: 'OpenWeatherMap API key updated successfully' });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// API endpoint to update admin token
app.post('/updateAdminToken', async (req, res) => {
    try {
        const { newToken } = req.body;
        adminToken = newToken;
        await AdminSettings.findOneAndUpdate({}, { newToken });
        res.json({ message: 'Admin token updated successfully' });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// API endpoint to update message frequency
app.post('/updateMessageFrequency', async (req, res) => {
    try {
        const { messageFrequency } = req.body;
        adminSettings.messageFrequency = messageFrequency;
        await AdminSettings.findOneAndUpdate({}, { messageFrequency });
        res.json({ message: 'Message frequency updated successfully' });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Endpoint to delete a user by chatId
app.delete('/deleteUser', async (req, res) => {
    try {
        const { chatId } = req.query.chatId;
        console.log(req.query.chatId)
        await User.findOneAndDelete({ chatId });
        res.json({ message: 'User deleted successfully' });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.listen(PORT, () => {
    console.log(`Server listening on port ${PORT}`);
});

// Telegram bot handling
bot.onText(/\/start/, async (msg) => {
    const chatId = msg.chat.id;

    const existingUser = await User.findOne({ chatId });

    if (existingUser) {
        if (existingUser.blocked) bot.sendMessage(chatId, 'Welcome Banned User');
        else bot.sendMessage(chatId, 'Welcome back!');
    } else {
        bot.sendMessage(chatId, 'Hi there! What is your name?');
        bot.once('text', async (msg) => {
            const name = msg.text;
            bot.sendMessage(chatId, `Nice to meet you, ${name}! What city are you in?`);

            bot.once('text', async (msg) => {
                const city = msg.text;
                bot.sendMessage(chatId, `Got it! What country are you in?`);

                bot.once('text', async (msg) => {
                    const country = msg.text;

                    // Save user information to MongoDB
                    const newUser = new User({ chatId, name, city, country });
                    await newUser.save();

                    bot.sendMessage(chatId, `Great! You're all set.`);
                    // Get and send the weather report
                    const weatherUpdate = await getWeatherUpdate(city, country);
                    bot.sendMessage(chatId, weatherUpdate);
                });
            });
        });
    }
});
