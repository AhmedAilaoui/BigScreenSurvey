// src/pages/AdminDashboard.js
import React, { useState, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useAdminAuth } from "../contexts/AdminAuthContext";
import { adminService } from "../services/AdminService";
import Responses from "./Responses";
import SurveyTable from "./SurveyTable";
import "./AdminDashboard.css";
import { Bar, Pie } from "react-chartjs-2";
// eslint-disable-next-line no-unused-vars
import { Chart as _ChartJS } from "chart.js/auto";

function AdminDashboard() {
  const navigate = useNavigate();
  const location = useLocation();
  const { admin, logout, isAuthenticated } = useAdminAuth();

  // États pour les statistiques
  const [statistics, setStatistics] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Vérifier l'authentification
  useEffect(() => {
    if (!isAuthenticated()) {
      navigate("/login");
      return;
    }
  }, [isAuthenticated, navigate]);

  // Charger les statistiques
  useEffect(() => {
    const loadStatistics = async () => {
      if (!isAuthenticated()) return;

      try {
        setLoading(true);
        const response = await adminService.getStatistics();

        if (response.data.success) {
          setStatistics(response.data.statistics);
        } else {
          throw new Error(
            response.data.message ||
              "Erreur lors du chargement des statistiques"
          );
        }
      } catch (error) {
        console.error("Erreur statistiques:", error);
        setError(
          error.response?.data?.message ||
            "Erreur lors du chargement des statistiques"
        );
      } finally {
        setLoading(false);
      }
    };

    if (location.pathname === "/admin") {
      loadStatistics();
    }
  }, [location.pathname, isAuthenticated]);

  const handleLogout = async () => {
    try {
      await logout();
      navigate("/login");
    } catch (error) {
      console.error("Erreur déconnexion:", error);
      navigate("/login"); // Rediriger même en cas d'erreur
    }
  };

  // Données par défaut si les statistiques ne sont pas encore chargées
  const getChartData = () => {
    if (!statistics?.quality?.data) {
      return {
        labels: ["Image", "Interface", "Réseau", "Graphismes", "Audio"],
        datasets: [
          {
            label: "Qualité (moyenne sur 5)",
            data: [0, 0, 0, 0, 0],
            backgroundColor: "#6c5ce7",
          },
        ],
      };
    }

    return {
      labels: statistics.quality.data.map((item) => item.label),
      datasets: [
        {
          label: "Qualité (moyenne sur 5)",
          data: statistics.quality.data.map((item) => item.value),
          backgroundColor: "#6c5ce7",
        },
      ],
    };
  };

  const getPieData = (questionNumber, defaultData) => {
    const questionKey = `question_${questionNumber}`;

    if (!statistics?.equipment?.[questionKey]?.data) {
      return defaultData;
    }

    const data = statistics.equipment[questionKey].data;
    return {
      labels: data.map((item) => item.label),
      datasets: [
        {
          data: data.map((item) => item.value),
          backgroundColor: [
            "#4834d4",
            "#ff7675",
            "#a29bfe",
            "#00b894",
            "#fdcb6e",
          ],
        },
      ],
    };
  };

  // Données par défaut pour les graphiques
  const defaultPieData = {
    labels: ["Aucune données"],
    datasets: [
      {
        data: [1],
        backgroundColor: ["#ddd"],
      },
    ],
  };

  if (!isAuthenticated()) {
    return <div>Redirection...</div>;
  }

  return (
    <div className="dashboard-container">
      <div className="sidebar">
        <div className="sidebar-header">
          <h2>BigScreen</h2>
          {admin && (
            <div
              className="admin-info"
              style={{
                fontSize: "12px",
                color: "#666",
                marginTop: "5px",
              }}
            >
              Connecté: {admin.name}
            </div>
          )}
        </div>
        <div className="sidebar-menu">
          <div
            className={`menu-item ${
              location.pathname === "/admin" ? "active" : ""
            }`}
            onClick={() => navigate("/admin")}
          >
            <span>Accueil</span>
          </div>
          <div
            className={`menu-item ${
              location.pathname === "/surveytable" ? "active" : ""
            }`}
            onClick={() => navigate("/surveytable")}
          >
            <span>Questionnaire</span>
          </div>
          <div
            className={`menu-item ${
              location.pathname === "/responses" ? "active" : ""
            }`}
            onClick={() => navigate("/responses")}
          >
            <span>Réponses</span>
          </div>
        </div>
        <div className="sidebar-footer">
          <div className="menu-item" onClick={handleLogout}>
            <span>Déconnexion</span>
          </div>
        </div>
      </div>

      <div className="main-content">
        {location.pathname === "/responses" ? (
          <Responses />
        ) : location.pathname === "/surveytable" ? (
          <SurveyTable />
        ) : (
          <div className="dashboard-content">
            <h2>Tableau de bord BigScreen</h2>

            {/* Statistiques générales */}
            {statistics?.summary && (
              <div
                className="summary-stats"
                style={{
                  display: "grid",
                  gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))",
                  gap: "20px",
                  marginBottom: "30px",
                }}
              >
                <div className="stat-card">
                  <h3>Total Questionnaires</h3>
                  <p
                    style={{
                      fontSize: "24px",
                      fontWeight: "bold",
                      color: "#4834d4",
                    }}
                  >
                    {statistics.summary.total_surveys}
                  </p>
                </div>
                <div className="stat-card">
                  <h3>Questionnaires Terminés</h3>
                  <p
                    style={{
                      fontSize: "24px",
                      fontWeight: "bold",
                      color: "#00b894",
                    }}
                  >
                    {statistics.summary.completed_surveys}
                  </p>
                </div>
                <div className="stat-card">
                  <h3>Taux de Completion</h3>
                  <p
                    style={{
                      fontSize: "24px",
                      fontWeight: "bold",
                      color: "#fdcb6e",
                    }}
                  >
                    {statistics.summary.completion_rate}%
                  </p>
                </div>
                <div className="stat-card">
                  <h3>Total Réponses</h3>
                  <p
                    style={{
                      fontSize: "24px",
                      fontWeight: "bold",
                      color: "#e17055",
                    }}
                  >
                    {statistics.summary.total_responses}
                  </p>
                </div>
              </div>
            )}

            {loading && (
              <div style={{ textAlign: "center", padding: "50px" }}>
                Chargement des statistiques...
              </div>
            )}

            {error && (
              <div
                style={{
                  color: "#e74c3c",
                  backgroundColor: "#fdf2f2",
                  padding: "15px",
                  borderRadius: "4px",
                  marginBottom: "20px",
                }}
              >
                {error}
              </div>
            )}

            <h3>Qualité de l'expérience BigScreen</h3>
            <div className="chart-container">
              <Bar
                data={getChartData()}
                options={{
                  responsive: true,
                  scales: {
                    y: {
                      beginAtZero: true,
                      max: 5,
                    },
                  },
                }}
              />
            </div>

            <div className="stats-grid">
              <div className="stat-card">
                <h3>Store de contenu VR (Q7)</h3>
                <div className="pie-chart">
                  <Pie
                    data={getPieData(7, defaultPieData)}
                    options={{ responsive: true }}
                  />
                </div>
              </div>
              <div className="stat-card">
                <h3>Usage principal de BigScreen (Q10)</h3>
                <div className="pie-chart">
                  <Pie
                    data={getPieData(10, defaultPieData)}
                    options={{ responsive: true }}
                  />
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

export default AdminDashboard;
