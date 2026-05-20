<style>
  .room-header {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
    justify-content: space-between;
  }

  .room-header__title small {
    margin-top: 0.35rem;
  }

  .room-tools {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
    justify-content: flex-end;
  }

  .room-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .room-filter {
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #0f172a;
    border-radius: 999px;
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.2s ease;
  }

  .room-filter:hover {
    border-color: #94a3b8;
  }

  .room-filter.active {
    background: #1d4ed8;
    color: #fff;
    border-color: #1d4ed8;
  }

  .room-filter .count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 0.35rem;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.08);
    font-weight: 700;
  }

  .room-filter.active .count {
    background: rgba(255, 255, 255, 0.2);
  }

  .room-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    font-size: 0.8rem;
    color: #64748b;
  }

  .room-legend .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
  }

  .room-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: 1rem;
  }

  .room-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .room-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 28px rgba(15, 23, 42, 0.12);
  }

  .room-card__media {
    position: relative;
    padding: 1rem;
    min-height: 120px;
    background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
  }

  .room-card__status {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: capitalize;
    color: #fff;
  }

  .room-card__number {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
  }

  .room-card__type {
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
  }

  .room-card__icon {
    position: absolute;
    bottom: 0;
    right: 8px;
    width: 90px;
    opacity: 0.85;
  }

  .room-card__body {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
  }

  .room-card__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    font-size: 0.8rem;
    color: #64748b;
  }

  .room-card__meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
  }

  .room-card__prices {
    display: grid;
    gap: 0.35rem;
    font-size: 0.82rem;
    color: #0f172a;
  }

  .room-card__prices .price-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .room-card__footer {
    padding: 0.75rem 1rem;
    border-top: 1px dashed #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.82rem;
    color: #0f172a;
  }

  .room-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    background: #f1f5f9;
    color: #0f172a;
  }

  .room-summary {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1rem;
  }

  .room-summary__title {
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.6rem;
  }

  .room-summary__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.88rem;
    color: #334155;
    padding: 0.25rem 0;
  }

  .room-summary__total {
    font-weight: 800;
    font-size: 1rem;
    color: #0f172a;
  }

  .status-libre {
    background: #16a34a;
    color: #fff;
  }

  .status-occupee {
    background: #dc2626;
    color: #fff;
  }

  .status-reservee {
    background: #f59e0b;
    color: #fff;
  }

  .status-libre-dot {
    background: #16a34a;
  }

  .status-reservee-dot {
    background: #f59e0b;
  }

  .status-occupee-dot {
    background: #dc2626;
  }

  .reservation-info {
    border: 1px dashed #e2e8f0;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    background: #ffffff;
    font-size: 0.85rem;
    color: #475569;
  }

  @media (max-width: 768px) {
    .room-header {
      align-items: stretch;
    }

    .room-tools {
      justify-content: flex-start;
    }

    .room-grid {
      grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    }
  }
</style>
