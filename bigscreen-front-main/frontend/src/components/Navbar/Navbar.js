import React from 'react';
import { Link } from 'react-router-dom';
import './Navbar.css';

function Navbar() {
  return (
    <nav className="navbar">
      <div className="navbar-container">
        <Link to="/" className="navbar-brand">
          BigScreen
        </Link>
        <div className="navbar-links">
          <Link to="/" className="nav-link">Survey</Link>
          <Link to="/login" className="nav-link">Log in</Link>
        </div>
      </div>
    </nav>
  );
}

export default Navbar;