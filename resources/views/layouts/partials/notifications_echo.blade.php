{{-- Real-time notifications component --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Only initialize Echo if it exists (loaded in bootstrap.js)
        if (window.Echo && {{ auth()->check() ? 'true' : 'false' }}) {
            // Listen for notifications on the user's private channel
            window.Echo.private('App.Models.User.{{ auth()->id() }}')
                .notification((notification) => {
                    // Handle issue comment notifications
                    if (notification.type === 'App\\Notifications\\IssueCommentAddedNotification') {
                        // Create toast notification
                        const toast = document.createElement('div');
                        toast.className = 'fixed bottom-4 right-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 max-w-md z-50 animate-fade-in';
                        toast.innerHTML = `
                            <div class="flex items-start gap-3">
                                <div class="shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">New Comment on Your Issue</h4>
                                    <p class="text-gray-700 dark:text-gray-300 text-sm mt-1">
                                        ${notification.commenter_name} commented on your issue "${notification.title}".
                                    </p>
                                    <div class="mt-3 flex justify-end">
                                        <a href="/issues/${notification.issue_id}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                            View Comment
                                        </a>
                                    </div>
                                </div>
                                <button onclick="this.parentElement.parentElement.remove()" class="shrink-0 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        `;
                        document.body.appendChild(toast);
                        
                        // Auto-remove after 10 seconds
                        setTimeout(() => {
                            toast.remove();
                        }, 10000);
                    }
                    // Handle status updated notifications
                    else if (notification.type === 'App\\Notifications\\StatusUpdatedNotification') {
                        // Create toast notification
                        const toast = document.createElement('div');
                        toast.className = 'fixed bottom-4 right-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 max-w-md z-50 animate-fade-in';
                        toast.innerHTML = `
                            <div class="flex items-start gap-3">
                                <div class="shrink-0 w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">Issue Status Updated</h4>
                                    <p class="text-gray-700 dark:text-gray-300 text-sm mt-1">
                                        ${notification.updater_name} updated your issue "${notification.title}" status from ${notification.old_status} to ${notification.new_status}.
                                    </p>
                                    <div class="mt-3 flex justify-end">
                                        <a href="/issues/${notification.issue_id}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                            View Issue
                                        </a>
                                    </div>
                                </div>
                                <button onclick="this.parentElement.parentElement.remove()" class="shrink-0 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        `;
                        document.body.appendChild(toast);
                        
                        // Auto-remove after 10 seconds
                        setTimeout(() => {
                            toast.remove();
                        }, 10000);
                    }
                    // Handle issue resolved notifications
                    else if (notification.type === 'App\\Notifications\\IssueResolvedNotification') {
                        // Create toast notification
                        const toast = document.createElement('div');
                        toast.className = 'fixed bottom-4 right-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 max-w-md z-50 animate-fade-in';
                        toast.innerHTML = `
                            <div class="flex items-start gap-3">
                                <div class="shrink-0 w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">Issue Resolved</h4>
                                    <p class="text-gray-700 dark:text-gray-300 text-sm mt-1">
                                        Your issue "${notification.title}" has been resolved by ${notification.resolver_name}.
                                    </p>
                                    <div class="mt-3 flex justify-end">
                                        <a href="/issues/${notification.issue_id}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                            View Resolution
                                        </a>
                                    </div>
                                </div>
                                <button onclick="this.parentElement.parentElement.remove()" class="shrink-0 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        `;
                        
                        // Add to DOM
                        document.body.appendChild(toast);
                        
                        // Auto-remove after 10 seconds
                        setTimeout(() => {
                            toast.classList.add('animate-fade-out');
                            setTimeout(() => toast.remove(), 500);
                        }, 10000);
                    }
                });
                
            // Listen for issue resolved events on the issue channel if we're on an issue page
            const issueIdMatch = window.location.pathname.match(/\/issues\/(\d+)/);
            if (issueIdMatch) {
                const issueId = issueIdMatch[1];
                
                window.Echo.private(`issues.${issueId}`)
                    .listen('.issue.resolved', (e) => {
                        // If we're on the issue page that was just resolved, update the UI without requiring refresh
                        if (window.location.pathname === `/issues/${e.id}`) {
                            // Create resolution section if it doesn't exist
                            let resolutionSection = document.querySelector('#resolution-section');
                            if (!resolutionSection) {
                                const mainContent = document.querySelector('.max-w-5xl.mx-auto.space-y-6');
                                if (mainContent) {
                                    // Create new resolution section
                                    resolutionSection = document.createElement('div');
                                    resolutionSection.id = 'resolution-section';
                                    resolutionSection.className = 'rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/50 p-6';
                                    resolutionSection.innerHTML = `
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Resolution</h3>
                                        <div class="prose dark:prose-invert max-w-none">
                                            <p class="whitespace-pre-wrap">${e.resolution}</p>
                                        </div>
                                        <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                            Resolved by ${e.resolver_name} • Just now
                                        </div>
                                    `;
                                    
                                    // Insert after description
                                    const descriptionSection = document.querySelector('.prose.dark\\:prose-invert.max-w-none').closest('.rounded-2xl');
                                    if (descriptionSection && descriptionSection.nextElementSibling) {
                                        mainContent.insertBefore(resolutionSection, descriptionSection.nextElementSibling);
                                    } else if (descriptionSection) {
                                        mainContent.appendChild(resolutionSection);
                                    }
                                }
                            } else {
                                // Update existing resolution section
                                const resolutionText = resolutionSection.querySelector('.prose.dark\\:prose-invert.max-w-none p');
                                const resolverInfo = resolutionSection.querySelector('.mt-3.text-sm');
                                
                                if (resolutionText) resolutionText.textContent = e.resolution;
                                if (resolverInfo) resolverInfo.textContent = `Resolved by ${e.resolver_name} • Just now`;
                            }
                            
                            // Update status badge
                            const statusBadge = document.querySelector('.inline-flex.items-center.px-3.py-1.rounded-full.text-xs.font-medium.bg-white\\/20:nth-of-type(2) span');
                            if (statusBadge) {
                                statusBadge.textContent = 'Resolved';
                            }
                        }
                    });
            }
        }
    });
</script>

{{-- Toast animations --}}
<style>
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }
    
    .animate-fade-out {
        animation: fadeOut 0.5s ease-out forwards;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes fadeOut {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(20px); }
    }
</style>