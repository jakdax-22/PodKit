import { API_URL } from "@/main"; 
import AXIOS from "@/utils/axiosInstance"

const messageService = {
    async fetchMessages(selfId, chatUserId) {
        try {
          const response = await AXIOS.get(`${API_URL}?action=getMessages&id_emisor=${selfId}&id_receptor=${chatUserId}`);
          return response;
        } catch (error) {
          throw error;
        }
      },
      async sendMessage(selfId, chatUserId, newMessage) {
        const formData = new FormData();
        formData.append('id_emisor', selfId);
        formData.append('id_receptor', chatUserId);
        formData.append('mensaje', newMessage);
      
        try {
          const response = await AXIOS.post(`${API_URL}?action=sendMessage`, formData);
          return response;
        } catch (error) {
          throw error;
        }
      }            
}

export default messageService