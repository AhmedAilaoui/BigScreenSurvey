// src/services/adminService.js
import apiClient, { getCsrfCookie } from "../api/config";

export const adminService = {
  // Authentification
  login: async (credentials) => {
    await getCsrfCookie();
    return apiClient.post("/admin/login", credentials);
  },
  logout: async () => {
    await getCsrfCookie();
    return apiClient.post("/admin/logout");
  },
  getProfile: async () => {
    await getCsrfCookie();
    return apiClient.get("/admin/me");
  },

  // Statistiques
  getStatistics: async () => {
    await getCsrfCookie();
    return apiClient.get("/admin/statistics");
  },
  getSpecificStats: async (type) => {
    await getCsrfCookie();
    return apiClient.get(`/admin/statistics/${type}`);
  },
};
