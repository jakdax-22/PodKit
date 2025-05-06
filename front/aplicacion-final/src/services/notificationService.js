import { API_URL } from "@/main"; 
import AXIOS from "@/utils/axiosInstance";

const notificationService = {
    async fetchNotifications(userId) {
        try {
          const response = await AXIOS.get(`${API_URL}?action=readNotifications&userId=${userId}`);
          return response;
        } catch (error) {
          throw error;
        }
      },
      async markAsRead(userId, notificationId) {
        const formData = new FormData();
        formData.append('id', userId);
        formData.append('notificationId', notificationId);
      
        try {
          const response = await AXIOS.post(`${API_URL}?action=deleteNotification`, formData);
          return response;
        } catch (error) {
          throw error;
        }
      }
            
}

export default notificationService