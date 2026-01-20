// src/App.js
import {
  BrowserRouter as Router,
  Routes,
  Route,
  Navigate,
} from "react-router-dom";
import { AdminAuthProvider } from "./contexts/AdminAuthContext";
import Login from "./pages/Login";
import AdminDashboard from "./pages/AdminDashboard";
import SurveyTable from "./pages/SurveyTable";
import Responses from "./pages/Responses";
import Survey from "./pages/Survey";
import SurveyResults from "./pages/SurveyResults";

function App() {
  return (
    <AdminAuthProvider>
      <Router>
        <div className="App">
          <Routes>
            {/* Redirection par défaut vers le questionnaire public */}
            <Route path="/" element={<Navigate to="/survey" />} />

            {/* Route publique pour le questionnaire */}
            <Route path="/survey" element={<Survey />} />
            <Route path="/survey-results" element={<SurveyResults />} />

            {/* Routes d'administration */}
            <Route path="/login" element={<Login />} />
            <Route path="/admin" element={<AdminDashboard />} />
            <Route path="/surveytable" element={<SurveyTable />} />
            <Route path="/responses" element={<Responses />} />

            {/* Route de fallback */}
            <Route path="*" element={<Navigate to="/survey" />} />
          </Routes>
        </div>
      </Router>
    </AdminAuthProvider>
  );
}

export default App;
