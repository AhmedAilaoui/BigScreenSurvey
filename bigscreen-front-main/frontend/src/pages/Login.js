// src/pages/Login.js
import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAdminAuth } from "../contexts/AdminAuthContext";
import "./Login.css";

function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);
  const navigate = useNavigate();
  const { login, loading, error, setError, isAuthenticated } = useAdminAuth();

  // Si déjà connecté, rediriger vers admin
  useEffect(() => {
    if (isAuthenticated()) {
      navigate("/admin");
    }
  }, [isAuthenticated, navigate]);

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (isSubmitting) return;

    setIsSubmitting(true);
    setError(null);

    try {
      await login({ email, password });
      navigate("/admin");
    } catch (error) {
      console.error("Erreur de connexion:", error);
      // L'erreur est déjà gérée par le contexte
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="login-container">
      <form className="login-form" onSubmit={handleSubmit}>
        <h2>Administration BigScreen</h2>

        {error && (
          <div
            className="error-message"
            style={{
              color: "#e74c3c",
              backgroundColor: "#fdf2f2",
              border: "1px solid #e74c3c",
              borderRadius: "4px",
              padding: "10px",
              marginBottom: "15px",
              fontSize: "14px",
            }}
          >
            {error}
          </div>
        )}

        <div className="form-group">
          <label htmlFor="email"></label>
          <input
            placeholder="Email"
            type="email"
            id="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            disabled={isSubmitting || loading}
          />
        </div>

        <div className="form-group">
          <label htmlFor="password"></label>
          <input
            placeholder="Mot de passe"
            type="password"
            id="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            disabled={isSubmitting || loading}
          />
        </div>

        <div className="forgot-password">
          <input type="checkbox" id="remember" />
          <label htmlFor="remember">Se souvenir de moi</label>
        </div>

        <button
          type="submit"
          disabled={isSubmitting || loading}
          style={{
            opacity: isSubmitting || loading ? 0.7 : 1,
            cursor: isSubmitting || loading ? "not-allowed" : "pointer",
          }}
        >
          {isSubmitting || loading ? "Connexion..." : "Se connecter"}
        </button>

        <div
          className="demo-credentials"
          style={{
            marginTop: "20px",
            padding: "10px",
            backgroundColor: "#f8f9fa",
            borderRadius: "4px",
            fontSize: "12px",
            color: "#666",
          }}
        >
          <strong>Comptes de démonstration :</strong>
          <br />
          • ahmedailaoui@bigscreen.com / password123
          <br />• hichemlassoued@bigscreen.com / password1234
        </div>

        <div style={{ marginTop: "20px", textAlign: "center" }}>
          <button
            type="button"
            onClick={() => navigate("/survey")}
            style={{
              background: "none",
              border: "none",
              color: "#6c5ce7",
              textDecoration: "underline",
              cursor: "pointer",
              marginLeft:"0px",
            }}
          >
            Retour au questionnaire public
          </button>
        </div>
      </form>
    </div>
  );
}

export default Login;
