// src/contexts/AdminAuthContext.js
import React, { createContext, useState, useContext, useEffect } from "react";
import { adminService } from "../services/AdminService";

const AdminAuthContext = createContext();

export const useAdminAuth = () => {
  const context = useContext(AdminAuthContext);
  if (!context) {
    throw new Error("useAdminAuth must be used within AdminAuthProvider");
  }
  return context;
};

export const AdminAuthProvider = ({ children }) => {
  const [admin, setAdmin] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Vérifier si l'admin est déjà connecté au chargement
  useEffect(() => {
    const checkAuthStatus = async () => {
      const token = localStorage.getItem("admin_token");
      const savedAdmin = localStorage.getItem("admin_user");

      if (token && savedAdmin) {
        try {
          setAdmin(JSON.parse(savedAdmin));
          // Optionnel : vérifier que le token est toujours valide
          await adminService.getProfile();
        } catch (error) {
          console.error("Token invalid:", error);
          localStorage.removeItem("admin_token");
          localStorage.removeItem("admin_user");
        }
      }
      setLoading(false);
    };

    checkAuthStatus();
  }, []);

  const login = async (credentials) => {
    setLoading(true);
    setError(null);

    try {
      const response = await adminService.login(credentials);

      if (response.data.success) {
        const { token, admin: adminData } = response.data;

        // Sauvegarder les données d'authentification
        localStorage.setItem("admin_token", token);
        localStorage.setItem("admin_user", JSON.stringify(adminData));
        setAdmin(adminData);

        return response.data;
      } else {
        throw new Error(response.data.message || "Erreur de connexion");
      }
    } catch (error) {
      const errorMessage =
        error.response?.data?.message || "Erreur de connexion";
      setError(errorMessage);
      throw error;
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    try {
      await adminService.logout();
    } catch (error) {
      console.error("Erreur lors de la déconnexion:", error);
    } finally {
      // Nettoyer les données locales même en cas d'erreur
      localStorage.removeItem("admin_token");
      localStorage.removeItem("admin_user");
      setAdmin(null);
    }
  };

  const isAuthenticated = () => {
    return admin !== null && localStorage.getItem("admin_token") !== null;
  };

  const value = {
    admin,
    loading,
    error,
    login,
    logout,
    isAuthenticated,
    setError,
  };

  return (
    <AdminAuthContext.Provider value={value}>
      {children}
    </AdminAuthContext.Provider>
  );
};
