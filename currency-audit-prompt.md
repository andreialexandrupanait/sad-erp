# Currency Handling Audit & Alignment Prompt


 


## Context


 


We operate a **live ERP application** in production that handles:


- Invoices (issued and received)


- Payments and collections


- Financial reports


- Mixed currency transactions (RON & EUR)


 


The system contains **real financial data** and is actively used for business operations.


 


---


 


## Objective


 


Perform a comprehensive audit to verify that our currency handling logic is:


- ✅ **Correct** — calculations follow proper accounting rules


- ✅ **Consistent** — same logic applied across all modules


- ✅ **Audit-safe** — historical data remains immutable and traceable


- ✅ **Compliant** — aligned with Romanian accounting standards and practices (e.g., SmartBill, Saga, similar systems)


 


---


 


## Scope of Audit


 


### 1. Invoice Currency Handling


- How is currency stored on invoice documents?


- How are amounts in foreign currency captured vs. RON equivalents?


- What exchange rate is used and when is it locked?


- Are tax calculations (TVA) performed in RON or original currency?


 


### 2. Payment & Collection Logic


- How are payments matched to invoices in different currencies?


- How are partial payments handled across currencies?


- Are exchange rate differences calculated at payment time?


- How is the "remaining balance" calculated for multi-currency invoices?


 


### 3. Exchange Rate Usage


- Source of exchange rates (BNR, manual, real-time API)?


- When is the rate captured (invoice date, payment date, document date)?


- Are historical rates preserved or recalculated?


- How are rate differences (diferențe de curs) recorded?


 


### 4. Reporting & Totals


- Are all reports consolidated in RON (accounting currency)?


- How are open balances calculated for foreign currency invoices?


- Do dashboards show commercial values (original currency) vs. accounting values (RON)?


- Are exchange rate gains/losses visible in financial reports?


 


---


 


## Deliverables


 


### Phase 1: Discovery (Required Before Any Implementation)


 


Produce a **structured list of clarifying questions** that must be answered to guarantee correctness. Organize questions by:


 


1. **Data Model Questions**


   - How are currencies stored at the document level?


   - What fields exist for amounts (original, converted, rate)?


   - Is exchange rate stored per line item or per document?


 


2. **Business Logic Questions**


   - When exactly are conversions performed?


   - Who/what determines the exchange rate to use?


   - How are rounding differences handled?


 


3. **Historical Data Questions**


   - Can past documents be modified?


   - Are exchange rates on old documents ever updated?


   - How is audit trail maintained?


 


4. **Integration Questions**


   - How does this interact with accounting exports?


   - Are there external systems depending on these values?


 


### Phase 2: Analysis & Recommendations (After Clarification)


 


Once questions are answered, provide:


 


1. **Current State Assessment**


   - Document identified issues and risks


   - Classify by severity (Critical / High / Medium / Low)


   - Map potential future problems


 


2. **Corrected Currency Model**


   - Required fields per document type


   - When each field should be populated


   - Immutability rules


 


3. **Conversion Rules Specification**


   - Clear rules for when conversions happen


   - Which rate source to use in each scenario


   - How to handle rate differences (diferențe de curs valutar)


 


4. **Reporting Logic**


   - RON as base accounting currency


   - How to display dual-currency values


   - Exchange difference reporting


 


5. **Safe Migration Strategy**


   - Steps to implement changes without breaking production


   - How to handle existing data (NO modification of historical records)


   - Rollback plan


 


---


 


## Hard Constraints


 


| Constraint | Requirement |


|------------|-------------|


| Production Safety | Application is live — no breaking changes allowed |


| Accounting Currency | RON must be the official accounting currency |


| Foreign Currency | EUR (and others) are commercial/display currencies only |


| Historical Integrity | **Absolutely NO recalculation or modification of past documents** |


| Audit Trail | All changes must be traceable |


| Tax Compliance | TVA calculations must remain correct and auditable |


 


---


 


## Romanian Accounting Context


 


For reference, standard Romanian ERP behavior typically includes:


 


- **BNR Exchange Rate**: Official rate from Banca Națională a României


- **Invoice Date Rate**: Exchange rate locked at invoice issue date


- **Payment Date Rate**: Separate rate captured at payment for difference calculation


- **Diferențe de Curs**: Exchange rate differences recorded as financial income/expense


- **All Accounting in RON**: Official books maintained in national currency


- **Dual Display**: Documents often show both original currency and RON equivalent


 


---


 


## Expected Output Format


 


### For Questions Phase:


```markdown


## Clarifying Questions


 


### Category: [Data Model / Business Logic / etc.]


 


1. **Question title**


   - Why this matters: [explanation]


   - Possible implications: [what could go wrong]


```


 


### For Recommendations Phase:


```markdown


## Finding: [Issue Title]


- **Severity**: Critical / High / Medium / Low


- **Current Behavior**: [what happens now]


- **Risk**: [what could go wrong]


- **Recommendation**: [what should change]


- **Migration Impact**: [how to fix safely]


```


 


---


 


## Approach Guidelines


 


1. **Do NOT jump to implementation** — understand first, propose second


2. **Think like an auditor** — accuracy over speed


3. **Consider edge cases** — partial payments, refunds, corrections


4. **Respect immutability** — historical data is sacred


5. **Plan for rollback** — every change should be reversible


 


---


 


## Success Criteria


 


- [ ] All critical questions identified and answered


- [ ] Current implementation fully documented


- [ ] All currency-related risks catalogued


- [ ] Proposed model covers all document types


- [ ] Migration plan preserves all historical data


- [ ] Solution aligns with Romanian accounting standards


- [ ] No disruption to live operations


 


---


 


*This audit should be conducted with the mindset of a senior ERP/accounting architect. The goal is a bulletproof currency handling system that will pass any financial audit.*