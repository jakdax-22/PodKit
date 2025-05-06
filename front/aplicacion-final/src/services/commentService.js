import { API_URL } from "@/main"; 
import AXIOS from "@/utils/axiosInstance"

const commentService = {
    async fetchComments(podcastId, currentPage, pageSize) {
        try {
          const response = await AXIOS.get(`${API_URL}?action=getComments&podcastId=${podcastId}&page=${currentPage}&pageSize=${pageSize}`);
          return response;
        } catch (error) {
          throw error;
        }
    },
    async submitComment(podcastId, content, userId) {
        const formData = new FormData();
        formData.append("podcastId", podcastId);
        formData.append("content", content);
        formData.append("userId", userId);
      
        try {
          const response = await AXIOS.post(`${API_URL}?action=addComment`, formData);
          return response;
        } catch (error) {
          throw error;
        }
      }
      
      
}

export default commentService