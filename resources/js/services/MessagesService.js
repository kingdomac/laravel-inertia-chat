const broadcastRoomMessage = async (message) => {
    try {
        await axios.post("/broadcast-room-message", {
            message: message,
        });
    } catch (error) {
        throw error;
    }
};

const markAsReadMessages = async (userId) => {
    try {
        await axios.put(`/mark-as-read-messages/${userId}`);
    } catch (error) {
        throw error;
    }
};

const countUsersNewMessages = async (userIdArray) => {
    try {
        return await axios.post("/count-users-new-messages", {
            usersId: userIdArray,
        });
    } catch (error) {
        throw error;
    }
};

const countUserNewMessages = async (userId) => {
    try {
        return await axios.get(`/count-user-new-messages/${userId}`);
    } catch (error) {
        throw error;
    }
};

const getChatMessages = async (userId) => {
    try {
        return await axios.get(`/messages/${userId}`);
    } catch (error) {}
};

const sendChatMessage = async (userId, message) => {
    try {
        return await axios.post("/broadcast-private-message", {
            message: message,
            user: parseInt(userId),
        });
    } catch (error) {
        throw error;
    }
};

export {
    broadcastRoomMessage,
    markAsReadMessages,
    countUsersNewMessages,
    getChatMessages,
    sendChatMessage,
    countUserNewMessages,
};
